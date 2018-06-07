<?php

namespace ZF\Doctrine\GraphQL;

use DateTime;

return [
    'service_manager' => [
        'invokables' => [
            Hydrator\Strategy\ToBoolean::class => Hydrator\Strategy\ToBoolean::class,
            Hydrator\Strategy\ToFloat::class => Hydrator\Strategy\ToFloat::class,
            Hydrator\Strategy\ToInteger::class => Hydrator\Strategy\ToInteger::class,
        ],
        'factories' => [
            Field\FieldResolver::class => Field\FieldResolverFactory::class,
            Filter\Loader::class => Filter\LoaderFactory::class,
            Filter\FilterManager::class => Filter\FilterManagerFactory::class,
            Filter\Criteria\Loader::class => Filter\Criteria\LoaderFactory::class,
            Filter\Criteria\FilterManager::class => Filter\Criteria\FilterManagerFactory::class,
            Resolve\ResolveManager::class => Resolve\ResolveManagerFactory::class,
            Resolve\Loader::class => Resolve\LoaderFactory::class,
            Type\Loader::class => Type\LoaderFactory::class,
            Type\TypeManager::class => Type\TypeManagerFactory::class,
        ],
    ],

    'hydrators' => array(
        'abstract_factories' => array(
            Hydrator\DoctrineHydratorFactory::class,
        ),
    ),

    'zf-doctrine-graphql-type' => [
        'invokables' => [
            DateTime::class => Type\DateTimeType::class,
        ],
        'abstract_factories' => [
            Type\EntityTypeAbstractFactory::class,
        ],
    ],

    'zf-doctrine-graphql-filter' => [
        'abstract_factories' => [
            Filter\FilterTypeAbstractFactory::class,
        ],
    ],

    'zf-doctrine-graphql-filter-criteria' => [
        'abstract_factories' => [
            Filter\Criteria\FilterTypeAbstractFactory::class,
        ],
    ],

    'zf-doctrine-graphql-resolve' => [
        'abstract_factories' => [
            Resolve\EntityResolveAbstractFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            Console\HydratorConfigurationSkeletonController::class
                => Console\HydratorConfigurationSkeletonControllerFactory::class,
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'graphql-hydrator-skeleton' => [
                    'type' => 'simple',
                    'options' => [
                        'route'    => 'graphql:hydrator:config-skeleton [--object-manager=]',
                        'defaults' => [
                            'controller' => Console\HydratorConfigurationSkeletonController::class,
                            'action' => 'index'
                        ],
                    ],
                ],
            ],
        ],
    ],
];
