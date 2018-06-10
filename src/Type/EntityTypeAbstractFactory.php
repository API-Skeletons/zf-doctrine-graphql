<?php

namespace ZF\Doctrine\GraphQL\Type;

use DateTime;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Instantiator\Instantiator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use ZF\Doctrine\GraphQL\Filter\Criteria\FilterManager;
use ZF\Doctrine\GraphQL\Field\FieldResolver;
use ZF\Doctrine\Criteria\Builder as CriteriaBuilder;

final class EntityTypeAbstractFactory implements
    AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this->canCreate($services, $requestedName);
    }

    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this($services, $requestedName);
    }

    /**
     * Loop through all configured ORM managers and if the passed $requestedName
     * as entity name is managed by the ORM return true;
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);

        return isset($config['zf-doctrine-graphql-hydrator'][$hydratorAlias]);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : EntityType
    {
        $name = null;
        $objectManagerAlias = null;
        $hydratorAlias = null;
        $fieldMetadata = null;
        $fields = [];
        $config = $container->get('config');
        $hydratorManager = $container->get('HydratorManager');
        $typeManager = $container->get(TypeManager::class);
        $fieldResolver = $container->get(FieldResolver::class);
        $criteriaFilterManager = $container->get(FilterManager::class);
        $criteriaBuilder = $container->get(CriteriaBuilder::class);

        foreach ($config['zf-rest'] as $controllerName => $restConfig) {
            if ($restConfig['entity_class'] == $requestedName) {
                $name = $restConfig['service_name'];
                $listener = $restConfig['listener'];
                $hydratorAlias = $config['zf-apigility']['doctrine-connected'][$listener]['hydrator'];
                $objectManagerAlias = $config['zf-apigility']['doctrine-connected'][$listener]['object_manager'];
                break;
            }
        }

        if (! $name) {
            throw new Exception("Rest config not found for entity ${requestedName}");
        }

        $objectManager = $container->get($objectManagerAlias);
        $hydrator = $hydratorManager->get($hydratorAlias);

        // Create an instance of the entity in order to get fields from the hydrator.
        $instantiator = new Instantiator();
        $entity = $instantiator->instantiate($requestedName);
        $entityFields = array_keys($hydrator->extract($entity));
        $references = [];

        $classMetadata = $objectManager->getClassMetadata($requestedName);

        foreach ($entityFields as $fieldName) {
            $graphQLType = null;
            try {
                $fieldMetadata = $classMetadata->getFieldMapping($fieldName);
            } catch (MappingException $e) {
                try {
                    $associationMetadata = $classMetadata->getAssociationMapping($fieldName);

                    switch ($associationMetadata['type']) {
                        case ClassMetadataInfo::ONE_TO_ONE:
                        case ClassMetadataInfo::MANY_TO_ONE:
                        case ClassMetadataInfo::TO_ONE:
                            $targetEntity = $associationMetadata['targetEntity'];
                            $references[$fieldName] = function () use ($typeManager, $targetEntity) {
                                return [
                                    'type' => $typeManager->get($targetEntity),
                                ];
                            };
                            break;
                        case ClassMetadataInfo::ONE_TO_MANY:
                        case ClassMetadataInfo::MANY_TO_MANY:
                        case ClassMetadataInfo::TO_MANY:
                            $targetEntity = $associationMetadata['targetEntity'];
                            $references[$fieldName] = function () use (
                                $config,
                                $typeManager,
                                $criteriaFilterManager,
                                $fieldResolver,
                                $targetEntity,
                                $objectManager,
                                $criteriaBuilder,
                                $hydratorManager
                            ) {
                                return [
                                    'type' => Type::listOf($typeManager->get($targetEntity)),
                                    'args' => [
                                        'filter' => $criteriaFilterManager->get($targetEntity),
                                    ],
                                    'resolve' => function (
                                        $source,
                                        $args,
                                        $context,
                                        ResolveInfo $resolveInfo
                                    ) use (
                                        $config,
                                        $fieldResolver,
                                        $objectManager,
                                        $criteriaBuilder,
                                        $hydratorManager
                                    ) {
                                        $collection = $fieldResolver($source, $args, $context, $resolveInfo);

                                        // Do not process empty collections
                                        if (! $collection->count()) {
                                            return null;
                                        }

                                        $filter = $args['filter'] ?? [];
                                        $filterArray = [];
                                        $orderByArray = [];
                                        $skip = 0;
                                        $limit = $config['zf-doctrine-graphql']['limit'];
                                        foreach ($filter as $field => $value) {
                                            if ($field == '_skip') {
                                                $skip = $value;
                                                continue;
                                            }

                                            if ($field == '_limit') {
                                                $limit = $value;
                                                continue;
                                            }

                                            if (strstr($field, '_')) {
                                                $field = strtok($field, '_');
                                                $filter = strtok('_');

                                                if ($filter == 'orderby') {
                                                    $orderByArray[] = [
                                                        'type' => 'field',
                                                        'field' => $field,
                                                        'direction' => $value,
                                                    ];
                                                } else {
                                                    $value['type'] = $filter;
                                                    $value['field'] = $field;
                                                    $filterArray[] = $value;
                                                }
                                            } else {
                                                $filterArray[] = [
                                                    'type' => 'eq',
                                                    'field' => $field,
                                                    'value' => $value,
                                                    'where' => 'and',
                                                    'format' => 'Y-m-d\TH:i:sP',
                                                ];
                                            }
                                        }

                                        $entityClassName = ClassUtils::getRealClass(get_class($collection->first()));
                                        $metadata = $objectManager->getClassMetadata($entityClassName);
                                        $criteria = $criteriaBuilder->create($metadata, $filterArray, $orderByArray);

                                        if ($skip) {
                                            $criteria->setFirstResult($skip);
                                        }

                                        if ($limit) {
                                            if ($config['zf-doctrine-graphql']['limit'] < $limit) {
                                                $limit = $config['zf-doctrine-graphql']['limit'];
                                            }
                                            $criteria->setMaxResults($limit);
                                        }

                                        //Rebuild collection using hydrators
                                        $entityClassName = ClassUtils::getRealClass(get_class($collection->first()));
                                        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\'
                                            . str_replace('\\', '_', $entityClassName);
                                        $hydrator = $hydratorManager->get($hydratorAlias);

                                        foreach ($collection as $key => $value) {
                                            $collection[$key] = $hydrator->extract($value);
                                        }

                                        return $collection->matching($criteria);
                                    },
                                ];
                            };
                            break;
                    }

                    continue;
                } catch (MappingException $e) {
                    continue;
                }
            }

            switch ($fieldMetadata['type']) {
                case 'tinyint':
                case 'smallint':
                case 'integer':
                case 'int':
                case 'bigint':
                    $graphQLType = Type::int();
                    break;
                case 'boolean':
                    $graphQLType = Type::boolean();
                    break;
                case 'decimal':
                case 'float':
                    $graphQLType = Type::float();
                    break;
                case 'string':
                case 'text':
                    $graphQLType = Type::string();
                    break;
                case 'datetime':
                    $graphQLType = $container->get(TypeManager::class)->get(DateTime::class);
                    break;
                default:
                    // Do not process unknown for now
                    $graphQLType = null;
                    break;
            }

            if ($graphQLType && $classMetadata->isIdentifier($fieldMetadata['fieldName'])) {
                $graphQLType = Type::id();
            }

            if ($graphQLType && ! $classMetadata->isNullable($fieldMetadata['fieldName'])) {
                $graphQLType = Type::nonNull($graphQLType);
            }

            if ($graphQLType) {
                $fields[$fieldName] = [
                    'type' => $graphQLType,
                    'description' => 'building...',
                ];
            }
        }

        return new EntityType([
            'name' => $name,
            'description' => 'testing description',
            'fields' => function () use ($fields, $references) {
                foreach ($references as $referenceName => $resolve) {
                    $fields[$referenceName] = $resolve();
                }

                return $fields;
            },
        ]);
    }
}
