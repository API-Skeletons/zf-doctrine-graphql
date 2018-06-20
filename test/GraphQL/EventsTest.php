<?php

namespace ZFTest\Doctrine\GraphQL\GraphQL;

use ZFTest\Doctrine\GraphQL\AbstractTest;
use GraphQL\GraphQL;
use ZF\Doctrine\GraphQL\Resolve\EntityResolveAbstractFactory;
use Zend\EventManager\Event;
use Db\Entity;

class EventsTest extends AbstractTest
{
    public function testUserEntity()
    {
        foreach ($this->schemaDataProvider() as $schema) {
            $container = $this->getApplication()->getServiceManager();
            $events = $container->get('SharedEventManager');

            $events->attach(
                EntityResolveAbstractFactory::class,
                EntityResolveAbstractFactory::FILTER_QUERY_BUILDER,
                function(Event $event)
                {
                    switch ($event->getParam('entityClassName')) {
                        case 'DbTest\Entity\Performance':
                            $event->getParam('queryBuilder')
                                ->andWhere('row.id = 1')
                                ;
                            break;
                        default:
                            break;
                    }
                },
                100
            );

            $query = "{ performance { id } }";

            $result = GraphQL::executeQuery($schema, $query);
            $output = $result->toArray();

            $this->assertEquals(1, sizeof($output['data']['performance']));
        }
    }
}
