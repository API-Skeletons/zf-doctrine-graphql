<?php

namespace ZF\Doctrine\GraphQL\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;

/**
 * Transform a number value into a php native integer
 *
 * @returns integer
 */
class ToInteger extends AbstractCollectionStrategy implements
    StrategyInterface
{
    public function extract($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return intval($value);
    }

    /**
     * @codeCoverageIgnore
     */
    public function hydrate($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return intval($value);
    }
}
