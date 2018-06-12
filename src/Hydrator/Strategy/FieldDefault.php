<?php

namespace ZF\Doctrine\GraphQL\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;

/**
 * Return the same value
 */
class FieldDefault extends AbstractCollectionStrategy implements
    StrategyInterface
{
    public function extract($value)
    {
        return $value;
    }

    /**
     * @codeCoverageIgnore
     */
    public function hydrate($value)
    {
        return $value;
    }
}
