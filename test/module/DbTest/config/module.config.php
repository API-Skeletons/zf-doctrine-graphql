<?php

return [
    'doctrine' => [
        'driver' => [
            'db_driver' => [
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\XmlDriver',
                'paths' => [
                    __DIR__ . '/orm',
                ],
            ],
            'orm_default' => [
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\DriverChain',
                'drivers' => [
                    'DbTest\\Entity' => 'db_driver',
                ],
            ],
        ],
    ],
];
