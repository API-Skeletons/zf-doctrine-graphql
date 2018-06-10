<?php

namespace ZF\Doctrine\GraphQL\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;
use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;
use ZF\Hal\Collection;

/**
 * Take no action on an association.  This class exists to
 * differentiate associations inside generated config.
 */
class AssociationDefault extends AbstractCollectionStrategy implements
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
