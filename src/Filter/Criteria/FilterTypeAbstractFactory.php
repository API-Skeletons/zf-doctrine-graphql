<?php

namespace ZF\Doctrine\GraphQL\Filter\Criteria;

use ArrayObject;
use DateTime;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Instantiator\Instantiator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

use ZF\Doctrine\GraphQL\Type\TypeManager;
use ZF\Doctrine\GraphQL\Filter\Criteria\Type as FilterTypeNS;

final class FilterTypeAbstractFactory implements
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

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : FilterType
    {
        $config = $container->get('config');
        $hydratorManager = $container->get('HydratorManager');
        $typeManager = $container->get(TypeManager::class);

        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);
        $hydratorConfig = $config['zf-doctrine-graphql-hydrator'][$hydratorAlias];

        $objectManager = $container->get($hydratorConfig['object_manager']);
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
                    /*
                    $associationMetadata = $classMetadata->getAssociationMapping($fieldName);

                    switch ($associationMetadata['type']) {
                        case ClassMetadataInfo::ONE_TO_ONE:
                        case ClassMetadataInfo::MANY_TO_ONE:
                            $targetEntity = $associationMetadata['targetEntity'];
                            $references[$fieldName] = function () use ($typeManager, $targetEntity) {
                                return [
                                    'type' => $typeManager->get($targetEntity),
                                ];
                            };
                            break;
                        case ClassMetadataInfo::ONE_TO_MANY:
                        case ClassMetadataInfo::MANY_TO_MANY:
                            $targetEntity = $associationMetadata['targetEntity'];
                            $references[$fieldName] = function () use ($typeManager, $targetEntity) {
                                return [
                                    'type' => Type::listOf($typeManager->get($targetEntity)),
                                ];
                            };
                            break;
                        case ClassMetadataInfo::TO_ONE:
                            break;
                        case ClassMetadataInfo::TO_MANY:
                            break;
                    }
                    */
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

            if ($graphQLType) {
                $fields[$fieldName] = [
                    'name' => $fieldName,
                    'type' => $graphQLType,
                    'description' => 'building...',
                ];

                // Add filters
                $fields[$fieldName . '_eq'] = [
                    'name' => $fieldName . '_eq',
                    'type' => new FilterTypeNS\EqFilterType(['fields' => [
                        'value' => [
                            'name' => 'value',
                            'type' => Type::nonNull($graphQLType)
                        ],
                    ]]),
                ];
                /******
                $fields[$fieldName . '_neq'] = [
                    'name' => $fieldName . '_neq',
                    'type' => new FilterTypeNS\NeqFilterType(['fields' => [
                        'value' => [
                            'name' => 'value',
                            'type' => Type::nonNull($graphQLType),
                        ],
                    ]]),
                ];
                $fields[$fieldName . '_lt'] = [
                    'name' => $fieldName . '_lt',
                    'type' => new FilterTypeNS\LtFilterType(['fields' => [
                        'value' => [
                            'name' => 'value',
                            'type' => Type::nonNull($graphQLType),
                        ],
                    ]]),
                ];
                $fields[$fieldName . '_lte'] = [
                    'name' => $fieldName . '_lte',
                    'type' => new FilterTypeNS\LteFilterType(['fields' => [
                        'value' => [
                            'name' => 'value',
                            'type' => Type::nonNull($graphQLType),
                        ],
                    ]]),
                ];
                $fields[$fieldName . '_gt'] = [
                    'name' => $fieldName . '_gt',
                    'type' => new FilterTypeNS\GtFilterType(['fields' => [
                        'value' => [
                            'name' => 'value',
                            'type' => Type::nonNull($graphQLType),
                        ],
                    ]]),
                ];
                $fields[$fieldName . '_gte'] = [
                    'name' => $fieldName . '_gte',
                    'type' => new FilterTypeNS\GteFilterType(['fields' => [
                        'value' => [
                            'name' => 'value',
                            'type' => Type::nonNull($graphQLType),
                        ],
                    ]]),
                ];
                $fields[$fieldName . '_isnull'] = [
                    'name' => $fieldName . '_isnull',
                    'type' => new FilterTypeNS\IsNullFilterType(),
                ];
                $fields[$fieldName . '_isnotnull'] = [
                    'name' => $fieldName . '_isnotnull',
                    'type' => new FilterTypeNS\IsNotNullFilterType(),
                ];
                $fields[$fieldName . '_in'] = [
                    'name' => $fieldName . '_in',
                    'type' => new FilterTypeNS\InFilterType(['fields' => [
                        'values' => [
                            'name' => 'values',
                            'type' => Type::listOf(Type::nonNull($graphQLType)),
                        ],
                    ]]),
                ];
                $fields[$fieldName . '_notin'] = [
                    'name' => $fieldName . '_notin',
                    'type' => new FilterTypeNS\NotInFilterType(['fields' => [
                        'values' => [
                            'name' => 'values',
                            'type' => Type::listOf(Type::nonNull($graphQLType)),
                        ],
                    ]]),
                ];
                $fields[$fieldName . '_between'] = [
                    'name' => $fieldName . '_between',
                    'type' => new FilterTypeNS\BetweenFilterType(['fields' => [
                        'from' => [
                            'name' => 'from',
                            'type' => Type::nonNull($graphQLType),
                        ],
                        'to' => [
                            'name' => 'to',
                            'type' => Type::nonNull($graphQLType),
                        ],
                    ]]),
                ];
                $fields[$fieldName . '_like'] = [
                    'name' => $fieldName . '_like',
                    'type' => new FilterTypeNS\LikeFilterType(),
                ];
                *****/
            }
        }

#        $fields['_debug'] = [
#            'name' => '_debug',
#            'type' => new FilterTypeNS\DebugQueryFilterType(),
#        ];

        return new FilterType([
            'name' => str_replace('\\', '_', $requestedName) . 'CriteriaFilter',
            'fields' => function () use ($fields, $references) {
                foreach ($references as $referenceName => $resolve) {
                    $fields[$referenceName] = $resolve();
                }

                return $fields;
            },
        ]);
    }
}
