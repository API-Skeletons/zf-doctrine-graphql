<?php

namespace ZF\Doctrine\GraphQL\QueryProvider;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

interface QueryProviderInterface
{
    /**
     * @param ResourceEvent $event
     * @return QueryBuilder
     */
    public function createQuery(ObjectManager $objectManager) : QueryBuilder;

}
