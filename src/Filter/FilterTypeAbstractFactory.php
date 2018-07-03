<?php

namespace ZF\Doctrine\GraphQL\Filter;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\Type;
use ZF\Doctrine\QueryBuilder\Filter\Service\ORMFilterManager;
use ZF\Doctrine\QueryBuilder\OrderBy\Service\ORMOrderByManager;
use ZF\Doctrine\Criteria\Filter\Service\FilterManager as CriteriaFilterManager;
use ZF\Doctrine\GraphQL\Type\TypeManager;
use ZF\Doctrine\GraphQL\Filter\Type as FilterTypeNS;
use ZF\Doctrine\GraphQL\AbstractAbstractFactory;
use ZF\Doctrine\GraphQL\Event;

final class FilterTypeAbstractFactory extends AbstractAbstractFactory implements
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

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : FilterType
    {
        // @codeCoverageIgnoreStart
        if ($this->isCached($requestedName, $options)) {
            return $this->getCache($requestedName, $options);
        }
        // @codeCoverageIgnoreEnd

        parent::__invoke($container, $requestedName, $options);

        $fields = [];
        $config = $container->get('config');
        $typeManager = $container->get(TypeManager::class);
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);
        $hydratorExtractTool = $container->get('ZF\\Doctrine\\GraphQL\\Hydrator\\HydratorExtractTool');
        $objectManager = $container
            ->get(
                $config['zf-doctrine-graphql-hydrator'][$hydratorAlias][$options['hydrator_section']]['object_manager']
            );
        $filterManager = $container->get(ORMFilterManager::class);
        $criteriaFilterManager = $container->get(CriteriaFilterManager::class);
        $orderByManager = $container->get(ORMOrderByManager::class);

        // Get an array of the hydrator fields
        $entityFields = $hydratorExtractTool->getFieldArray($requestedName, $hydratorAlias, $options);

        $references = [];

        $classMetadata = $objectManager->getClassMetadata($requestedName);

        foreach ($entityFields as $fieldName) {
            $graphQLType = null;
            try {
                $fieldMetadata = $classMetadata->getFieldMapping($fieldName);
            } catch (MappingException $e) {
                // For all related data you cannot query on them from the top level resource
                continue;
            }

            $graphQLType = $this->mapFieldType($fieldMetadata['type']);

            if ($graphQLType && $classMetadata->isIdentifier($fieldMetadata['fieldName'])) {
                $graphQLType = Type::id();
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
                if ($orderByManager->has('field')) {
                    $fields[$fieldName . '_sort'] = [
                        'name' => $fieldName . '_sort',
                        'type' => Type::string(),
                        'description' => 'Sort the result either ASC or DESC',
                    ];
                }

                if ($filterManager->has('eq')) {
                    $fields[$fieldName] = [
                        'name' => $fieldName,
                        'type' => $graphQLType,
                        'description' => 'Equals; same as name: value.  DateTime not supported.',
                    ];

                    // Add filters
                    $fields[$fieldName . '_eq'] = [
                        'name' => $fieldName . '_eq',
                        'type' => $graphQLType,
                        'description' => 'Equals; same as name: value.  DateTime not supported.',
                    ];
                }

                if ($filterManager->has('neq')) {
                    $fields[$fieldName . '_neq'] = [
                        'name' => $fieldName . '_neq',
                        'type' => $graphQLType,
                        'description' => 'Not Equals',
                    ];
                }

                if ($filterManager->has('lt')) {
                    $fields[$fieldName . '_lt'] = [
                        'name' => $fieldName . '_lt',
                        'type' => $graphQLType,
                        'description' => 'Less Than',
                    ];
                }

                if ($filterManager->has('lte')) {
                    $fields[$fieldName . '_lte'] = [
                        'name' => $fieldName . '_lte',
                        'type' => $graphQLType,
                        'description' => 'Less Than or Equal To',
                    ];
                }

                if ($filterManager->has('gt')) {
                    $fields[$fieldName . '_gt'] = [
                        'name' => $fieldName . '_gt',
                        'type' => $graphQLType,
                        'description' => 'Greater Than',
                    ];
                }

                if ($filterManager->has('gte')) {
                    $fields[$fieldName . '_gte'] = [
                        'name' => $fieldName . '_gte',
                        'type' => $graphQLType,
                        'description' => 'Greater Than or Equal To',
                    ];
                }

                if ($filterManager->has('isnull')) {
                    $fields[$fieldName . '_isnull'] = [
                        'name' => $fieldName . '_isnull',
                        'type' => Type::boolean(),
                        'description' => 'Takes a boolean.  If TRUE return results where the field is null. '
                            . 'If FALSE returns results where the field is not null. '
                            . 'NOTE: acts as "isEmpty" for collection filters.  A value of false will '
                            . 'be handled as though it were null.',
                    ];
                }

                if ($filterManager->has('in')) {
                    $fields[$fieldName . '_in'] = [
                        'name' => $fieldName . '_in',
                        'type' => Type::listOf(Type::nonNull($graphQLType)),
                        'description' => 'Filter for values in an array',
                    ];
                }

                if ($filterManager->has('notin')) {
                    $fields[$fieldName . '_notin'] = [
                        'name' => $fieldName . '_notin',
                        'type' => Type::listOf(Type::nonNull($graphQLType)),
                        'description' => 'Filter for values not in an array',
                    ];
                }

                if ($filterManager->has('between')) {
                    $fields[$fieldName . '_between'] = [
                        'name' => $fieldName . '_between',
                        'description' => 'Filter between `from` and `to` values.  Good substitute for DateTime Equals.',
                        'type' => new FilterTypeNS\Between(['fields' => [
                            'from' => [
                                'name' => 'from',
                                'type' => Type::nonNull($graphQLType),
                            ],
                            'to' => [
                                'name' => 'to',
                                'type' => Type::nonNull($graphQLType),
                            ],
                        ]
                        ]),
                    ];
                }

                if ($filterManager->has('like') && $graphQLType == Type::string()) {
                    $fields[$fieldName . '_contains'] = [
                        'name' => $fieldName . '_contains',
                        'type' => Type::string(),
                        'description' => 'Strings only. Similar to a Like query as `like \'%value%\'`',
                    ];

                    $fields[$fieldName . '_startswith'] = [
                        'name' => $fieldName . '_startswith',
                        'type' => Type::string(),
                        'documentation' => 'Strings only. '
                            . 'A like query from the beginning of the value `like \'value%\'`',
                    ];

                    $fields[$fieldName . '_endswith'] = [
                        'name' => $fieldName . '_endswith',
                        'type' => Type::string(),
                        'documentation' => 'Strings only. '
                            . 'A like query from the end of the value `like \'%value\'`',
                    ];
                }

                if ($criteriaFilterManager->has('memberof')) {
                    $fields[$fieldName . '_memberof'] = [
                        'name' => $fieldName . '_memberof',
                        'type' => Type::string(),
                        'description' => 'Matches a value in an array field.',
                    ];
                }
            }
            $fields[$fieldName . '_distinct'] = [
                'name' => $fieldName . '_distinct',
                'type' => Type::boolean(),
                'description' => 'Return a unique list of fieldName.  Only one distinct fieldName allowed per filter.',
            ];
        }

        $fields['_skip'] = [
            'name' => '_skip',
            'type' => Type::int(),
            'documentation' => 'Skip forward x records from beginning of data set.',
        ];
        $fields['_limit'] = [
            'name' => '_limit',
            'type' => Type::int(),
            'documentation' => 'Limit the number of results to x.',
        ];

        $instance = new FilterType([
            'name' => str_replace('\\', '_', $requestedName) . '__Filter',
            'fields' => function () use ($fields, $references) {
                foreach ($references as $referenceName => $resolve) {
                    // @codeCoverageIgnoreStart
                    // This works fine but may need bigger unit tests
                    $fields[$referenceName] = $resolve();
                    // @codeCoverageIgnoreEnd
                }

                return $fields;
            },
        ]);

        return $this->cache($requestedName, $options, $instance);
    }
}
