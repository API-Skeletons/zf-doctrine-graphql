<?php

namespace ZF\Doctrine\GraphQL\QueryProvider;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

interface QueryProviderInterface
{
    public function createQuery(ObjectManager $objectManager) : QueryBuilder;
}
