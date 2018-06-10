<?php

namespace ZF\Doctrine\GraphQL\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;
use ZF\Hal\Collection;

/**
 * Return the same value
 */
class FieldNone extends AbstractCollectionStrategy implements
    StrategyInterface
{
    public function extract($value)
    {
        return $value;
    }

    public function hydrate($value)
    {
        return $value;
    }
}
