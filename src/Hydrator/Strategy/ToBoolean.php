<?php

namespace ZF\Doctrine\GraphQL\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;

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
        if (is_null($value)) {
            return $value;
        }

        return (bool)$value;
    }

    /**
     * @codeCoverageIgnore
     */
    public function hydrate($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return (bool)$value;
    }
}
