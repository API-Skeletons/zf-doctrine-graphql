<?php

namespace ZF\Doctrine\GraphQL\Type;

use DateTime;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use ZF\Doctrine\Criteria\Builder as CriteriaBuilder;
use ZF\Doctrine\GraphQL\AbstractAbstractFactory;
use ZF\Doctrine\GraphQL\Criteria\CriteriaManager;
use ZF\Doctrine\GraphQL\Field\FieldResolver;
use ZF\Doctrine\GraphQL\Event;
use Doctrine\DBAL\Types\Type as ORMType;
use ZF\Doctrine\GraphQL\Type\CustomTypeInterface;

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
        // @codeCoverageIgnoreStart
        if ($this->isCached($requestedName, $options)) {
            return $this->getCache($requestedName, $options);
        }
        // @codeCoverageIgnoreEnd

        parent::__invoke($container, $requestedName, $options);

        $name = str_replace('\\', '_', $requestedName);
        $objectManagerAlias = null;
        $hydratorAlias = null;
        $fieldMetadata = null;
        $fields = [];
        $config = $container->get('config');
        $fieldResolver = $container->get(FieldResolver::class);
        $typeManager = $container->get(TypeManager::class);
        $criteriaFilterManager = $container->get(CriteriaManager::class);
        $criteriaBuilder = $container->get(CriteriaBuilder::class);
        $documentationProvider = $container->get('ZF\Doctrine\GraphQL\Documentation\DocumentationProvider');
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);
        $hydratorExtractTool = $container->get('ZF\\Doctrine\\GraphQL\\Hydrator\\HydratorExtractTool');
        $objectManager = $container
            ->get(
                $config['zf-doctrine-graphql-hydrator'][$hydratorAlias][$options['hydrator_section']]['object_manager']
            );

        // Get an array of the hydrator fields
        $entityFields = $hydratorExtractTool->getFieldArray($requestedName, $hydratorAlias, $options);

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
                        // @codeCoverageIgnoreStart
                        case ClassMetadataInfo::TO_MANY:
                        // @codeCoverageIgnoreEnd
                            $targetEntity = $associationMetadata['targetEntity'];
                            $references[$fieldName] = function () use (
                                $options,
                                $typeManager,
                                $criteriaFilterManager,
                                $fieldResolver,
                                $targetEntity,
                                $objectManager,
                                $criteriaBuilder,
                                $hydratorExtractTool
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
                                        $hydratorExtractTool
                                    ) {
                                        $collection = $fieldResolver($source, $args, $context, $resolveInfo);

                                        // It is better to process empty collections than extract an entire collection
                                        // just to get its count.  Lazy loading will fetch a whole collection to get
                                        // a count but extra lazy will not.
                                        // There was a check here for collection size; now removed.

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


                                            // Handle most fields as $field_$type: $value
                                            // Get right-most _text
                                            $filter = substr($field, strrpos($field, '_') + 1);
                                            if (strpos($field, '_') === false || ! $this->isFilter($filter)) {
                                                // Handle field:value
                                                 $filterArray[] = [
                                                     'type' => 'eq',
                                                     'field' => $field,
                                                     'value' => $value,
                                                 ];
                                            } elseif (strpos($field, '_') !== false && $this->isFilter($filter)) {
                                                $field = substr($field, 0, (int)strrpos($field, '_'));

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

                                        $entityClassName = $collection->getTypeClass()->name;
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
                                        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\'
                                            . str_replace('\\', '_', $entityClassName);
                                        $matching = $hydratorExtractTool
                                            ->extractToCollection(
                                                $collection->matching($criteria),
                                                $hydratorAlias,
                                                $options
                                            );

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
                // @codeCoverageIgnoreStart
                } catch (MappingException $e) {
                    continue;
                }
                // @codeCoverageIgnoreEnd
            }

            // Find GraphQL field type
            $typeManager = $container->get(TypeManager::class);
            $graphQLType = $this->mapFieldType($fieldMetadata['type'], $typeManager);

            if ($graphQLType && $classMetadata->isIdentifier($fieldMetadata['fieldName'])) {
                $graphQLType = Type::id();
            }

            if ($graphQLType && ! $classMetadata->isNullable($fieldMetadata['fieldName'])) {
                $graphQLType = Type::nonNull($graphQLType);
            }

            // Send event to allow overriding a field type
            $results = $this->getEventManager()->trigger(
                Event::MAP_FIELD_TYPE,
                $this,
                [
                    'fieldName' => $fieldName,
                    'graphQLType' => $graphQLType,
                    'classMetadata' => $classMetadata,
                    'fieldMetadata' => $fieldMetadata,
                    'hydratorAlias' => $hydratorAlias,
                    'options' => $options,
                ]
            );
            if ($results->stopped()) {
                $graphQLType = $results->last();
            }

            if ($graphQLType) {
                $fields[$fieldName] = [
                    'type' => $graphQLType,
                    'description' => $documentationProvider->getField($requestedName, $fieldName, $options),
                ];
            }
        }

        $instance = new EntityType([
            'name' => str_replace('\\', '_', $requestedName) . '__' . $options['hydrator_section'],
            'description' => $documentationProvider->getEntity($requestedName, $options),
            'fields' => function () use ($fields, $references) {
                foreach ($references as $referenceName => $resolve) {
                    $fields[$referenceName] = $resolve();
                }

                return $fields;
            },
        ]);

        return $this->cache($requestedName, $options, $instance);
    }
}
