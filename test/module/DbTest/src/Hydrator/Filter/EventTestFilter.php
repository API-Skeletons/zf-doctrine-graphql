<?php

namespace DbTest\Hydrator\Filter;

use Zend\Hydrator\Filter\FilterInterface;

class EventTestFilter implements FilterInterface
{
    public function filter($field)
    {
        $excludeFields = [
            'performance',
            'user',
        ];

        return (! in_array($field, $excludeFields));
    }
}
