<?php

namespace ZF\Doctrine\GraphQL\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;
use ZF\Hal\Collection;

/**
 * Transform a value into a php native boolean
 *
 * @returns float
 */
class ToBoolean extends AbstractCollectionStrategy implements
    StrategyInterface
{
    public function extract($value)
    {
        return (bool)$value;
    }

    public function hydrate($value)
    {
        return (bool)$value;
    }
}
