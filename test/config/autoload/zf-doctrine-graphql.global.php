<?php
return [
    'zf-doctrine-graphql' => [
        'limit' => 1000,
    ],
    'zf-doctrine-graphql-query-provider' => [
        'aliases' => [
            \DbTest\Entity\Artist::class => \DbTest\QueryProvider\Artist::class,
            \DbTest\Entity\Performance::class => \DbTest\QueryProvider\Performance::class,
            \DbTest\Entity\User::class => \DbTest\QueryProvider\User::class,
            \DbTest\Entity\Address::class => \DbTest\QueryProvider\Address::class,
        ],
        'invokables' => [
            \DbTest\QueryProvider\Artist::class => \DbTest\QueryProvider\Artist::class,
            \DbTest\QueryProvider\Performance::class => \DbTest\QueryProvider\Performance::class,
            \DbTest\QueryProvider\User::class => \DbTest\QueryProvider\User::class,
            \DbTest\QueryProvider\Address::class => \DbTest\QueryProvider\Address::class,
        ],
    ],

    'zf-doctrine-graphql-hydrator' => [
        'ZF\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Artist' => [
            'entity_class' => \DbTest\Entity\Artist::class,
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'by_value' => false,
            'use_generated_hydrator' => true,
            'naming_strategy' => null,
            'hydrator' => null,
            'strategies' => [
                'id' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                'name' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                'createdAt' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                'performance' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                'user' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
            ],
            'filters' => [
                'default' => [
                    'condition' => 'and',
                    'filter' => \ZF\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                ],
            ],
        ],
        'ZF\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_User' => [
            'entity_class' => \DbTest\Entity\User::class,
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'by_value' => false,
            'use_generated_hydrator' => true,
            'naming_strategy' => null,
            'hydrator' => null,
            'strategies' => [
                'id' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                'name' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                'artist' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                'address' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
            ],
            'filters' => [
                'default' => [
                    'condition' => 'and',
                    'filter' => \ZF\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                ],
            ],
        ],
        'ZF\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Address' => [
            'entity_class' => \DbTest\Entity\Address::class,
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'by_value' => false,
            'use_generated_hydrator' => true,
            'naming_strategy' => null,
            'hydrator' => null,
            'strategies' => [
                'id' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                'address' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                'user' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
            ],
            'filters' => [
                'default' => [
                    'condition' => 'and',
                    'filter' => \ZF\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                ],
            ],
        ],
        'ZF\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Performance' => [
            'entity_class' => \DbTest\Entity\Performance::class,
            'object_manager' => 'doctrine.entitymanager.orm_default',
            'by_value' => false,
            'use_generated_hydrator' => true,
            'naming_strategy' => null,
            'hydrator' => null,
            'strategies' => [
                'id' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                'performanceDate' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                'venue' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                'attendance' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                'isTradable' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToBoolean::class,
                'ticketPrice' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToFloat::class,
                'artist' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
            ],
            'filters' => [
                'default' => [
                    'condition' => 'and',
                    'filter' => \ZF\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                ],
            ],
        ],
    ],
];
