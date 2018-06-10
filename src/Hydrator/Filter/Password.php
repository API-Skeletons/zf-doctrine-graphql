<?php

namespace ZF\Doctrine\GraphQL\Hydrator\Filter;

use Zend\Hydrator\Filter\FilterInterface;

class Password implements FilterInterface
{
    public function filter($field)
    {
        $excludeFields = [
            'password'
        ];

        return (! in_array($field, $excludeFields));
    }
}
