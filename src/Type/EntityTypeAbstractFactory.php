<?php

namespace ZF\Doctrine\GraphQL\Type;

use DateTime;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Instantiator\Instantiator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use ZF\Doctrine\Criteria\Builder as CriteriaBuilder;
use ZF\Doctrine\GraphQL\AbstractAbstractFactory;
use ZF\Doctrine\GraphQL\Criteria\FilterManager;
use ZF\Doctrine\GraphQL\Field\FieldResolver;

final class EntityTypeAbstractFactory extends AbstractAbstractFactory implements
    AbstractFactoryInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this->canCreate($services, $requestedName);
    }

    /**
     * @codeCoverageIgnore
     */
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
        if ($this->isCached($requestedName, $options)) {
            return $this->getCache($requestedName, $options);
        }

        $name = str_replace('\\', '_', $requestedName);
        $objectManagerAlias = null;
        $hydratorAlias = null;
        $fieldMetadata = null;
        $fields = [];
        $config = $container->get('config');
        $hydratorManager = $container->get('HydratorManager');
        $fieldResolver = $container->get(FieldResolver::class);
        $typeManager = $container->get(TypeManager::class);
        $criteriaFilterManager = $container->get(FilterManager::class);
        $criteriaBuilder = $container->get(CriteriaBuilder::class);

        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);
        $hydratorConfig = $config['zf-doctrine-graphql-hydrator'][$hydratorAlias][$options['hydrator_section']];
        $hydrator = $hydratorManager->build($hydratorAlias, $options);
        $objectManager = $container->get($hydratorConfig['object_manager']);

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
                            $references[$fieldName] = function () use ($typeManager, $targetEntity, $options) {
                                return [
                                    'type' => $typeManager->build($targetEntity, $options),
                                ];
                            };
                            break;
                        case ClassMetadataInfo::ONE_TO_MANY:
                        case ClassMetadataInfo::MANY_TO_MANY:
                        case ClassMetadataInfo::TO_MANY:
                            $targetEntity = $associationMetadata['targetEntity'];
                            $references[$fieldName] = function () use (
                                $options,
                                $typeManager,
                                $criteriaFilterManager,
                                $fieldResolver,
                                $targetEntity,
                                $objectManager,
                                $criteriaBuilder,
                                $hydratorManager
                            ) {
                                return [
                                    'type' => Type::listOf($typeManager->build($targetEntity, $options)),
                                    'args' => [
                                        'filter' => $criteriaFilterManager->build($targetEntity, $options),
                                    ],
                                    'resolve' => function (
                                        $source,
                                        $args,
                                        $context,
                                        ResolveInfo $resolveInfo
                                    ) use (
                                        $options,
                                        $fieldResolver,
                                        $objectManager,
                                        $criteriaBuilder,
                                        $hydratorManager
                                    ) {
                                        $collection = $fieldResolver($source, $args, $context, $resolveInfo);

                                        // Do not process empty collections
                                        if (! sizeof($collection)) {
                                            return [];
                                        }

                                        $filter = $args['filter'] ?? [];
                                        $filterArray = [];
                                        $orderByArray = [];
                                        $distinctField = null;
                                        $skip = 0;
                                        $limit = $options['limit'];

                                        foreach ($filter as $field => $value) {
                                            if ($field == '_skip') {
                                                $skip = $value;
                                                continue;
                                            }

                                            if ($field == '_limit') {
                                                $limit = $value;
                                                continue;
                                            }

                                            if (! strstr($field, '_')) {
                                                $filterArray[] = [
                                                    'type' => 'eq',
                                                    'field' => $field,
                                                    'value' => $value,
                                                ];
                                            } else {
                                                $field = strtok($field, '_');
                                                $filter = strtok('_');

                                                switch ($filter) {
                                                    case 'sort':
                                                        $orderByArray[] = [
                                                            'type' => 'field',
                                                            'field' => $field,
                                                            'direction' => $value,
                                                        ];
                                                        break;
                                                    case 'in':
                                                        $filterArray[] = [
                                                            'type' => 'in',
                                                            'field' => $field,
                                                            'values' => $value,
                                                        ];
                                                        break;
                                                    case 'notin':
                                                        $filterArray[] = [
                                                            'type' => 'notin',
                                                            'field' => $field,
                                                            'values' => $value,
                                                        ];
                                                        break;
                                                    case 'between':
                                                        $filterArray[] = [
                                                            'type' => 'gte',
                                                            'field' => $field,
                                                            'value' => $value['from'],
                                                        ];
                                                        $filterArray[] = [
                                                            'type' => 'lte',
                                                            'field' => $field,
                                                            'value' => $value['to'],
                                                        ];
                                                        break;
                                                    case 'isnull':
                                                        if ($value == true) {
                                                            $filterArray[] = [
                                                                'type' => 'eq',
                                                                'field' => $field,
                                                                'value' => null,
                                                            ];
                                                        } else {
                                                            $filterArray[] = [
                                                                'type' => 'neq',
                                                                'field' => $field,
                                                                'value' => null,
                                                            ];
                                                        }
                                                        break;
                                                    case 'distinct':
                                                        if (! $distinctField && $value) {
                                                            $distinctField = $field;
                                                        }
                                                        break;
                                                    case 'memberof':
                                                    default:
                                                        $filterArray[] = [
                                                            'type' => $filter,
                                                            'field' => $field,
                                                            'value' => $value,
                                                        ];
                                                        break;
                                                }
                                            }
                                        }

                                        $entityClassName = ClassUtils::getRealClass(get_class($collection->first()));
                                        $metadata = $objectManager->getClassMetadata($entityClassName);

                                        foreach ($filterArray as $key => $filter) {
                                            $filterArray[$key]['format'] = 'Y-m-d\TH:i:sP';
                                        }

                                        $criteria = $criteriaBuilder->create($metadata, $filterArray, $orderByArray);

                                        if ($skip) {
                                            $criteria->setFirstResult($skip);
                                        }

                                        if ($limit) {
                                            if ($options['limit'] < $limit) {
                                                $limit = $options['limit'];
                                            }
                                            $criteria->setMaxResults($limit);
                                        }

                                        //Rebuild collection using hydrators
                                        $entityClassName = ClassUtils::getRealClass(get_class($collection->first()));
                                        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\'
                                            . str_replace('\\', '_', $entityClassName);
                                        $hydrator = $hydratorManager->build($hydratorAlias, $options);

                                        $data = new ArrayCollection();
                                        foreach ($collection as $key => $value) {
                                            $data->add($hydrator->extract($value));
                                        }

                                        $matching = $data->matching($criteria);

                                        if ($distinctField) {
                                            $distinctValueArray = [];
                                            foreach ($matching as $key => $value) {
                                                if (! in_array($value[$distinctField], $distinctValueArray)) {
                                                    $distinctValueArray[] = $value[$distinctField];
                                                } else {
                                                    unset($matching[$key]);
                                                }
                                            }
                                        }

                                        return $matching;
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

            $graphQLType = $this->mapFieldType($fieldMetadata['type']);
            // Override for datetime
            if ($fieldMetadata['type'] == 'datetime') {
                $graphQLType = $container->get(TypeManager::class)->get(DateTime::class);
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

        $instance = new EntityType([
            'name' => $requestedName . '_' . $options['hydrator_section'],
            'description' => 'testing description',
            'fields' => function () use ($fields, $references) {
                foreach ($references as $referenceName => $resolve) {
                    $fields[$referenceName] = $resolve();
                }

                return $fields;
            },
        ]);

        $this->cache($requestedName, $options, $instance);

        return $instance;
    }
}
