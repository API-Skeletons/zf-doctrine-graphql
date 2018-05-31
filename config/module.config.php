<?php

namespace ZF\Doctrine\GraphQL;

use DateTime;

return [
    'service_manager' => [
        'factories' => [
            Field\FieldResolver::class => Field\FieldResolverFactory::class,
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
