<?php

namespace ZF\Doctrine\GraphQL\Filter\Criteria\Type;

use GraphQL\Type\Definition\Type;

class EndsWith extends AbstractFilterType
{
    public function __construct(array $config = [])
    {
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
            'format' => [
                'name' => 'format',
                'type' => Type::string(),
                'defaultValue' => 'Y-m-d\TH:i:sP',
            ],
        ];

        $config['fields'] = array_merge($config['fields'], $defaultFieldConfig);

        parent::__construct($config);
    }
}
