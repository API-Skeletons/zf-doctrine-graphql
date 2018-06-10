<?php

namespace ZF\Doctrine\GraphQL\Hydrator\Filter;

use Zend\Hydrator\Filter\FilterInterface;

class FilterDefault implements FilterInterface
{
    public function filter($field)
    {
        $excludeFields = [
        ];

        return (! in_array($field, $excludeFields));
    }
}
