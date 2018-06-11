<?php

namespace ZF\Doctrine\Criteria;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'zf-doctrine-criteria-orderby' => [
        'aliases' => [
            'field' => OrderBy\Field::class,
        ],
        'factories' => [
            OrderBy\Field::class => InvokableFactory::class,
        ],
    ],
    'zf-doctrine-criteria-filter' => [
        'aliases' => [
            'contains'   => Filter\Contains::class,
            'endswith'   => Filter\EndsWith::class,
            'eq'         => Filter\Equals::class,
            'gt'         => Filter\GreaterThan::class,
            'gte'        => Filter\GreaterThanOrEquals::class,
            'in'         => Filter\In::class,
            'lt'         => Filter\LessThan::class,
            'lte'        => Filter\LessThanOrEquals::class,
            'memberof'   => Filter\MemberOf::class,
            'neq'        => Filter\NotEquals::class,
            'notin'      => Filter\NotIn::class,
            'startswith' => Filter\StartsWith::class,
        ],
        'factories' => [
            Filter\Contains::class            => InvokableFactory::class,
            Filter\EndsWith::class            => InvokableFactory::class,
            Filter\Equals::class              => InvokableFactory::class,
            Filter\GreaterThan::class         => InvokableFactory::class,
            Filter\GreaterThanOrEquals::class => InvokableFactory::class,
            Filter\In::class                  => InvokableFactory::class,
            Filter\LessThan::class            => InvokableFactory::class,
            Filter\LessThanOrEquals::class    => InvokableFactory::class,
            Filter\MemberOf::class            => InvokableFactory::class,
            Filter\NotEquals::class           => InvokableFactory::class,
            Filter\NotIn::class               => InvokableFactory::class,
            Filter\StartsWith::class          => InvokableFactory::class,
        ],
    ],
];
