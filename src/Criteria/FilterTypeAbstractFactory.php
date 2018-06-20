<?php

namespace ZF\Doctrine\GraphQL\Criteria;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Instantiator\Instantiator;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\Type;
use ZF\Doctrine\Criteria\Filter\Service\FilterManager;
use ZF\Doctrine\Criteria\OrderBy\Service\OrderByManager;
use ZF\Doctrine\GraphQL\Type\TypeManager;
use ZF\Doctrine\GraphQL\Criteria\Type as FilterTypeNS;
use ZF\Doctrine\GraphQL\AbstractAbstractFactory;

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
        if ($this->isCached($requestedName, $options)) {
            return $this->getCache($requestedName, $options);
        }

        $config = $container->get('config');
        $fields = [];
        $hydratorManager = $container->get('HydratorManager');
        $typeManager = $container->get(TypeManager::class);
        $filterManager = $container->get(FilterManager::class);
        $orderByManager = $container->get(OrderByManager::class);

        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);
        $hydratorConfig = $config['zf-doctrine-graphql-hydrator'][$hydratorAlias][$options['hydrator_section']];

        $objectManager = $container->get($hydratorConfig['object_manager']);
        $hydrator = $hydratorManager->get($hydratorAlias);

        // Create an instance of the entity in order to get fields from the hydrator.
        $instantiator = new Instantiator();
        $entity = $instantiator->instantiate($requestedName);
        $entityFields = array_keys($hydrator->extract($entity));

        $classMetadata = $objectManager->getClassMetadata($requestedName);

        foreach ($entityFields as $fieldName) {
            $graphQLType = null;
            try {
                $fieldMetadata = $classMetadata->getFieldMapping($fieldName);
            } catch (MappingException $e) {
                // For all related data you cannot query on them from the current resource
                continue;
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
                    $graphQLType = Type::string();
                    break;
                default:
                    // @codeCoverageIgnoreStart
                    // Do not process unknown.  If you have an unknown type please report it.
                    $graphQLType = null;
                    break;
                    // @codeCoverageIgnoreEnd
            }

            if ($graphQLType && $classMetadata->isIdentifier($fieldMetadata['fieldName'])) {
                $graphQLType = Type::id();
            }

            if ($graphQLType) {
                if ($orderByManager->has('field')) {
                    $fields[$fieldName . '_sort'] = [
                        'name' => $fieldName . '_sort',
                        'type' => Type::string(),
                        'description' => 'building...',
                    ];
                }

                // Add filters
                if ($filterManager->has('eq')) {
                    $fields[$fieldName] = [
                        'name' => $fieldName,
                        'type' => $graphQLType,
                        'description' => 'building...',
                    ];

                    $fields[$fieldName . '_eq'] = [
                        'name' => $fieldName . '_eq',
                        'type' => $graphQLType,
                        'description' => 'building...',
                    ];
                }

                if ($filterManager->has('neq')) {
                    $fields[$fieldName . '_neq'] = [
                        'name' => $fieldName . '_neq',
                        'type' => $graphQLType,
                        'description' => 'building...',
                    ];
                }

                if ($filterManager->has('lt')) {
                    $fields[$fieldName . '_lt'] = [
                        'name' => $fieldName . '_lt',
                        'type' => $graphQLType,
                        'description' => 'building...',
                    ];
                }

                if ($filterManager->has('lte')) {
                    $fields[$fieldName . '_lte'] = [
                        'name' => $fieldName . '_lte',
                        'type' => $graphQLType,
                        'description' => 'building...',
                    ];
                }

                if ($filterManager->has('gt')) {
                    $fields[$fieldName . '_gt'] = [
                        'name' => $fieldName . '_gt',
                        'type' => $graphQLType,
                        'description' => 'building...',
                    ];
                }

                if ($filterManager->has('gte')) {
                    $fields[$fieldName . '_gte'] = [
                        'name' => $fieldName . '_gte',
                        'type' => $graphQLType,
                        'description' => 'building...',
                    ];
                }

                if ($filterManager->has('isnull') && $filterManager->has('isnotnull')) {
                    $fields[$fieldName . '_isnull'] = [
                        'name' => $fieldName . '_isnull',
                        'type' => Type::boolean(),
                        'description' => 'building...',
                    ];
                }

                if ($filterManager->has('lte') && $filterManager->has('gte')) {
                    $fields[$fieldName . '_between'] = [
                        'name' => $fieldName . '_between',
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

                if ($filterManager->has('in')) {
                    $fields[$fieldName . '_in'] = [
                        'name' => $fieldName . '_in',
                        'type' => Type::listOf($graphQLType),
                        'description' => 'building...',
                    ];
                }

                if ($filterManager->has('notin')) {
                    $fields[$fieldName . '_notin'] = [
                        'name' => $fieldName . '_notin',
                        'type' => Type::listOf($graphQLType),
                        'description' => 'building...',
                    ];
                }

                if ($graphQLType == Type::string()) {
                    if ($filterManager->has('startswith')) {
                        $fields[$fieldName . '_startswith'] = [
                            'name' => $fieldName . '_startswith',
                            'type' => $graphQLType,
                            'description' => 'building...',
                        ];
                    }

                    if ($filterManager->has('endswith')) {
                        $fields[$fieldName . '_endswith'] = [
                            'name' => $fieldName . '_endswith',
                            'type' => $graphQLType,
                            'description' => 'building...',
                        ];
                    }

                    if ($filterManager->has('contains')) {
                        $fields[$fieldName . '_contains'] = [
                            'name' => $fieldName . '_contains',
                            'type' => $graphQLType,
                            'description' => 'building...',
                        ];
                    }
                }

                if ($filterManager->has('memberof')) {
                    $fields[$fieldName . '_memberof'] = [
                        'name' => $fieldName . '_memberof',
                        'type' => $graphQLType,
                        'description' => 'building...',
                    ];
                }
            }

            $fields[$fieldName . '_distinct'] = [
                'name' => $fieldName . '_distinct',
                'type' => Type::boolean(),
                'description' => 'building...',
            ];
        }

        $fields['_skip'] = [
            'name' => '_skip',
            'type' => Type::int(),
        ];
        $fields['_limit'] = [
            'name' => '_limit',
            'type' => Type::int(),
        ];

        $instance = new FilterType([
            'name' => str_replace('\\', '_', $requestedName) . 'CriteriaFilter',
            'fields' => function () use ($fields) {
                return $fields;
            },
        ]);

        $this->cache($requestedName, $options, $instance);

        return $instance;
    }
}
