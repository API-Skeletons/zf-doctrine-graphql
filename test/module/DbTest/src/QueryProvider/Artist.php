<?php

namespace DbTest\QueryProvider;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use ZF\Doctrine\GraphQL\QueryProvider\QueryProviderInterface;
use DbTest\Entity;

final class Artist implements
    QueryProviderInterface
{
    /**
     * @param ResourceEvent $event
     * @return QueryBuilder
     */
    public function createQuery(ObjectManager $objectManager) : QueryBuilder
    {
        $queryBuilder = $objectManager->createQueryBuilder();
        $queryBuilder
            ->select('row')
            ->from(Entity\Artist::class, 'row')
            ;

        return $queryBuilder;
    }
}
