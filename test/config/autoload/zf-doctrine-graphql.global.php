<?php
return [
    'zf-doctrine-graphql-hydrator' => [
        'ZF\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Artist' => [
            'default' => [
                'entity_class' => \DbTest\Entity\Artist::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'name' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'alias' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'createdAt' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'performance' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                    'user' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\NullifyOwningAssociation::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ZF\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
            'test' => [
                'entity_class' => \DbTest\Entity\Artist::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'alias' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'createdAt' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'name' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'performance' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                    'user' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\NullifyOwningAssociation::class,
                ],
                'filters' => [
                    'default' => [
                        'condition' => 'and',
                        'filter' => \ZF\Doctrine\GraphQL\Hydrator\Filter\FilterDefault::class,
                    ],
                ],
            ],
            'event' => [
                'entity_class' => \DbTest\Entity\Artist::class,
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'by_value' => false,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [
                    'id' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToInteger::class,
                    'alias' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\ToJson::class,
                    'createdAt' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'name' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault::class,
                    'performance' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\AssociationDefault::class,
                    'user' => \ZF\Doctrine\GraphQL\Hydrator\Strategy\NullifyOwningAssociation::class,
                ],
                'filters' => [
                    'eventTest' => [
                        'condition' => 'and',
                        'filter' => 'DbTest\Hydrator\Filter\EventTestFilter',
                    ],
                ],
            ],
        ],
        'ZF\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_User' => [
            'default' => [
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
                    'password' => [
                        'condition' => 'and',
                        'filter' => \ZF\Doctrine\GraphQL\Hydrator\Filter\Password::class,
                    ],
                ],
            ],
            'test' => [
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
                    'password' => [
                        'condition' => 'and',
                        'filter' => \ZF\Doctrine\GraphQL\Hydrator\Filter\Password::class,
                    ],
                ],
            ],
        ],
        'ZF\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Address' => [
            'default' => [
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
            'test' => [
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
        ],
        'ZF\\Doctrine\\GraphQL\\Hydrator\\DbTest_Entity_Performance' => [
            'default' => [
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
            'test' => [
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
    ],
];
