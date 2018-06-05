<?php

namespace ZF\Doctrine\GraphQL\Filter\Type;

use GraphQL\Type\Definition\Type;

class DebugQueryFilterType extends AbstractFilterType
{
    public function __construct(array $config = [])
    {
        $config['fields'] = [
            [
                'name' => 'value',
                'type' => Type::boolean(),
                'defaultValue' => true,
            ],
        ];

        parent::__construct($config);
    }
}
