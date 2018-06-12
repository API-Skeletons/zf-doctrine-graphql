<?php

namespace ZF\Doctrine\GraphQL;

use DateTime;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'service_manager' => [
        'invokables' => [
            Hydrator\Filter\FilterDefault::class => Hydrator\Filter\FilterDefault::class,
            Hydrator\Filter\Password::class => Hydrator\Filter\Password::class,
            Hydrator\Strategy\ToBoolean::class => Hydrator\Strategy\ToBoolean::class,
            Hydrator\Strategy\ToFloat::class => Hydrator\Strategy\ToFloat::class,
            Hydrator\Strategy\ToInteger::class => Hydrator\Strategy\ToInteger::class,
            Hydrator\Strategy\NullifyOwningAssociation::class => Hydrator\Strategy\NullifyOwningAssociation::class,
            Hydrator\Strategy\AssociationDefault::class => Hydrator\Strategy\AssociationDefault::class,
            Hydrator\Strategy\FieldDefault::class => Hydrator\Strategy\FieldDefault::class,
        ],
        'factories' => [
            Filter\Loader::class => Filter\LoaderFactory::class,
            Filter\FilterManager::class => Filter\FilterManagerFactory::class,
            Criteria\Loader::class => Criteria\LoaderFactory::class,
            Criteria\FilterManager::class => Criteria\FilterManagerFactory::class,
            Resolve\ResolveManager::class => Resolve\ResolveManagerFactory::class,
            Resolve\Loader::class => Resolve\LoaderFactory::class,
            Type\Loader::class => Type\LoaderFactory::class,
            Type\TypeManager::class => Type\TypeManagerFactory::class,
        ],
    ],

    'zf-doctrine-criteria-filter' => [
        'aliases' => [
            'between' => Filter\Criteria\Type\Between::class,
            'isnull' => Filter\Criteria\Type\IsNull::class,
            'isnotnull' => Filter\Criteria\Type\IsNotNull::class,
        ],
        'factories' => [
            Filter\Criteria\Type\Between::class => InvokableFactory::class,
            Filter\Criteria\Type\IsNull::class => InvokableFactory::class,
            Filter\Criteria\Type\IsNotNull::class => InvokableFactory::class,
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

    'zf-doctrine-graphql-criteria' => [
        'abstract_factories' => [
            Criteria\FilterTypeAbstractFactory::class,
        ],
    ],

    'zf-doctrine-graphql-resolve' => [
        'abstract_factories' => [
            Resolve\EntityResolveAbstractFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            Console\ConfigurationSkeletonController::class
                => Console\ConfigurationSkeletonControllerFactory::class,
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'graphql-skeleton' => [
                    'type' => 'simple',
                    'options' => [
                        'route'    => 'graphql:config-skeleton [--object-manager=]',
                        'defaults' => [
                            'controller' => Console\ConfigurationSkeletonController::class,
                            'action' => 'index'
                        ],
                    ],
                ],
            ],
        ],
    ],
];
