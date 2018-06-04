<?php

namespace ZF\Doctrine\GraphQL\Filter\Type;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InputType;

class NeqFilterType extends Type implements InputType
{
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->name = $config['name'];
        $this->astNode = isset($config['astNode']) ? $config['astNode'] : null;
        $this->description = isset($config['description']) ? $config['description'] : null;
    }

}
