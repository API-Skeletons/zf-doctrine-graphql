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

    'zf-doctrine-graphql-entity-manager' => [
        'doctrine.entitymanager.orm_default',
        'doctrine.entitymanager.orm_zf_doctrine_audit',
    ],
];
