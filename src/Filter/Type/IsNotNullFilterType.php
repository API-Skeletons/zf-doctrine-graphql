<?php

namespace ZF\Doctrine\GraphQL\Filter\Type;

use GraphQL\Type\Definition\Type;

class IsNotNullFilterType extends AbstractFilterType
{
    public function __construct(array $config = [])
    {
        $config['fields'] = $config['fields'] ?? [];

        $defaultFieldConfig = [
            'field' => [
                'name' => 'field',
                'type' => Type::string(),
            ],
            'where' => [
                'name' => 'where',
                'type' => Type::string(),
                'defaultValue' => 'and',
            ],
            'alias' => [
                'name' => 'alias',
                'type' => Type::string(),
                'defaultValue' => 'row',
            ],
        ];

        $config['fields'] = array_merge($config['fields'], $defaultFieldConfig);

        parent::__construct($config);
    }
}
