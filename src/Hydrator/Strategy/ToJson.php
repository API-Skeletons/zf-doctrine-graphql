<?php

namespace ZF\Doctrine\GraphQL\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;

/**
 * Transform a value to JSON
 *
 * @returns string
 */
class ToJson extends AbstractCollectionStrategy implements
    StrategyInterface
{
    public function extract($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @codeCoverageIgnore
     */
    public function hydrate($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return json_decode($value);
    }
}
