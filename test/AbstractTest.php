<?php

namespace ZFTest\Doctrine\GraphQL;

use Datetime;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use DbTest\Entity;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use ZF\Doctrine\GraphQL\Type\Loader as TypeLoader;
use ZF\Doctrine\GraphQL\Filter\Loader as FilterLoader;
use ZF\Doctrine\GraphQL\Resolve\Loader as ResolveLoader;
use ZF\Doctrine\GraphQL\Context;

abstract class AbstractTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/config/test.config.php'
        );
        parent::setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $objectManager = $serviceManager->get('doctrine.entitymanager.orm_default');
        $config = $serviceManager->get('config');

        // Create Default Database
        $metadata = $objectManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($objectManager);
        $sql = $schemaTool->getCreateSchemaSql($metadata);

        foreach ($sql as $command) {
            $objectManager->getConnection()->exec($command);
        }

        // Add fixtures
        $artist1 = new Entity\Artist();
        $artist1->name = 'artist1';
        $artist1->createdAt = new DateTime('2010-02-01');
        $artist1->alias = ['a1', 'a2', 'a3'];
        $objectManager->persist($artist1);

        $performance1 = new Entity\Performance();
        $performance1->artist = $artist1;
        $performance1->performanceDate = '2011-01-01';
        $performance1->venue = 'venue1';
        $performance1->attendance = 1000;
        $performance1->isTradable = true;
        $performance1->ticketPrice = 10.01;
        $objectManager->persist($performance1);
        $artist1->performance->add($performance1);

        $performance2 = new Entity\Performance();
        $performance2->artist = $artist1;
        $performance2->performanceDate = '2011-01-02';
        $performance2->venue = 'venue2';
        $performance2->attendance = 2000;
        $performance2->isTradable = null;
        $performance2->ticketPrice = 20.01;
        $objectManager->persist($performance2);
        $artist1->performance->add($performance2);

        $performance3 = new Entity\Performance();
        $performance3->artist = $artist1;
        $performance3->performanceDate = '2011-01-03';
        $performance3->venue = 'venue3';
        $performance3->attendance = 2000;
        $performance3->isTradable = false;
        $performance3->ticketPrice = 30.01;
        $objectManager->persist($performance3);
        $artist1->performance->add($performance3);

        $performance4 = new Entity\Performance();
        $performance4->artist = $artist1;
        $performance4->performanceDate = '2011-01-04';
        $performance4->venue = 'venue4';
        $performance4->attendance = 4000;
        $performance4->isTradable = false;
        $performance4->ticketPrice = 40.01;
        $objectManager->persist($performance4);
        $artist1->performance->add($performance4);

        $performance5 = new Entity\Performance();
        $performance5->artist = $artist1;
        $performance5->performanceDate = '2011-01-05';
        $performance5->venue = 'venue5';
        $performance5->attendance = 5000;
        $performance5->isTradable = true;
        $performance5->ticketPrice = 50.01;
        $objectManager->persist($performance5);
        $artist1->performance->add($performance5);

        $artist2 = new Entity\Artist();
        $artist2->name = 'artist2';
        $artist2->createdAt = new DateTime('2010-02-02');
        $artist2->alias = ['b1', 'b2', 'b3'];
        $objectManager->persist($artist2);

        $artist3 = new Entity\Artist();
        $artist3->name = 'artist3';
        $artist3->createdAt = new DateTime('2010-02-03');
        $artist3->alias = ['c1', 'c2', 'c3'];
        $objectManager->persist($artist3);

        $artist4 = new Entity\Artist();
        $artist4->name = 'artist4';
        $artist4->createdAt = new DateTime('2010-02-04');
        $artist4->alias = ['d1', 'd2', 'd3'];
        $objectManager->persist($artist4);

        $artist5 = new Entity\Artist();
        $artist5->name = 'artist5';
        $artist5->createdAt = new DateTime('2010-02-05');
        $artist5->alias = ['e1', 'e2', 'e3'];
        $objectManager->persist($artist5);


        $user1 = new Entity\User();
        $user1->name = 'test1';
        $user1->password = 'secret';
        $user1->createdAt = new DateTime('2010-01-01');

        $address = new Entity\Address();
        $address->user= $user1;
        $address->address = 'address1';
        $user1->address = $address;

        $objectManager->persist($address);
        $objectManager->persist($user1);

        $user = new Entity\User();
        $user->name = 'test2';
        $user->password = 'secret';
        $user->createdAt = new DateTime('2010-01-02');

        $address = new Entity\Address();
        $address->user= $user;
        $address->address = 'address2';
        $user->address = $address;

        $objectManager->persist($address);
        $objectManager->persist($user);

        $user = new Entity\User();
        $user->name = 'test3';
        $user->password = 'secret';
        $user->createdAt = new DateTime('2010-01-03');

        $address = new Entity\Address();
        $address->user= $user;
        $address->address = 'address3';
        $user->address = $address;

        $objectManager->persist($address);
        $objectManager->persist($user);

        $user = new Entity\User();
        $user->name = 'test4';
        $user->password = 'secret';
        $user->createdAt = new DateTime('2010-01-04');

        $address = new Entity\Address();
        $address->user= $user;
        $address->address = 'address4';
        $user->address = $address;

        $objectManager->persist($address);
        $objectManager->persist($user);

        $user = new Entity\User();
        $user->name = 'test5';
        $user->password = 'secret';
        $user->createdAt = new DateTime('2010-01-05');

        $address = new Entity\Address();
        $address->user= $user;
        $address->address = 'address5';
        $user->address = $address;

        $objectManager->persist($address);
        $objectManager->persist($user);

        $user1->artist->add($artist1);
        $artist1->user->add($user1);
        $user1->artist->add($artist2);
        $artist2->user->add($user1);
        $user1->artist->add($artist3);
        $artist3->user->add($user1);
        $user1->artist->add($artist4);
        $artist4->user->add($user1);
        $user1->artist->add($artist5);
        $artist5->user->add($user1);

        $objectManager->flush();
        $objectManager->clear();
    }

    protected function getObjectManager()
    {
        return $this->getApplication()
            ->getServiceManager()
            ->get('doctrine.entitymanager.orm_default')
            ;
    }

    public function schemaDataProvider() {
        $testContext = new Context();
        $testContext->setHydratorSection('test');
        $testContext->setUseHydratorCache(true);
        $testContext->setLimit(1000);

        $providers = [
            [
                'schemaName' => 'default',
                'context' => new Context(),
            ],
            [
                'schemaName' => 'test',
                'context' => $testContext,
            ],
        ];

        return $providers;
    }

    public function eventDataProvider() {
        $eventContext = new Context();
        $eventContext->setHydratorSection('event');
        $eventContext->setUseHydratorCache(false);
        $eventContext->setLimit(1000);

        $providers = [
            [
                'schemaName' => 'event',
                'context' => $eventContext,
            ],
        ];

        return $providers;
    }

    protected function getSchema($schemaName)
    {
        switch ($schemaName) {
            case 'default':
                return $this->getDefaultSchema();
            case 'test':
                return $this->getTestSchema();
            case 'event':
                return $this->getEventSchema();
        }
    }

    protected function getDefaultSchema()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        $typeLoader = $serviceManager->get(TypeLoader::class);
        $filterLoader = $serviceManager->get(FilterLoader::class);
        $resolveLoader = $serviceManager->get(ResolveLoader::class);

        $context = new Context();

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => Type::listOf($typeLoader(Entity\Artist::class, $context)),
                        'args' => [
                            'filter' => $filterLoader(Entity\Artist::class, $context),
                        ],
                        'resolve' => $resolveLoader(Entity\Artist::class, $context),
                    ],
                    'performance' => [
                        'type' => Type::listOf($typeLoader(Entity\Performance::class, $context)),
                        'args' => [
                            'filter' => $filterLoader(Entity\Performance::class, $context),
                        ],
                        'resolve' => $resolveLoader(Entity\Performance::class, $context),
                    ],
                    'user' => [
                        'type' => Type::listOf($typeLoader(Entity\User::class, $context)),
                        'args' => [
                            'filter' => $filterLoader(Entity\User::class, $context),
                        ],
                        'resolve' => $resolveLoader(Entity\User::class, $context),
                    ],
                    'address' => [
                        'type' => Type::listOf($typeLoader(Entity\Address::class, $context)),
                        'args' => [
                            'filter' => $filterLoader(Entity\Address::class, $context),
                        ],
                        'resolve' => $resolveLoader(Entity\Address::class, $context),
                    ],
                ],
            ]),
        ]);

        return $schema;
    }

    protected function getTestSchema()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        $typeLoader = $serviceManager->get(TypeLoader::class);
        $filterLoader = $serviceManager->get(FilterLoader::class);
        $resolveLoader = $serviceManager->get(ResolveLoader::class);

        $context = new Context();
        $context->setHydratorSection('test');
        $context->setUseHydratorCache(false);

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => Type::listOf($typeLoader(Entity\Artist::class, $context)),
                        'args' => [
                            'filter' => $filterLoader(Entity\Artist::class, $context),
                        ],
                        'resolve' => $resolveLoader(Entity\Artist::class, $context),
                    ],
                    'performance' => [
                        'type' => Type::listOf($typeLoader(Entity\Performance::class, $context)),
                        'args' => [
                            'filter' => $filterLoader(Entity\Performance::class, $context),
                        ],
                        'resolve' => $resolveLoader(Entity\Performance::class, $context),
                    ],
                    'user' => [
                        'type' => Type::listOf($typeLoader(Entity\User::class, $context)),
                        'args' => [
                            'filter' => $filterLoader(Entity\User::class, $context),
                        ],
                        'resolve' => $resolveLoader(Entity\User::class, $context),
                    ],
                    'address' => [
                        'type' => Type::listOf($typeLoader(Entity\Address::class, $context)),
                        'args' => [
                            'filter' => $filterLoader(Entity\Address::class, $context),
                        ],
                        'resolve' => $resolveLoader(Entity\Address::class, $context),
                    ],
                ],
            ]),
        ]);

        return $schema;
    }

    protected function getEventSchema()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        $typeLoader = $serviceManager->get(TypeLoader::class);
        $filterLoader = $serviceManager->get(FilterLoader::class);
        $resolveLoader = $serviceManager->get(ResolveLoader::class);

        $context = new Context();
        $context->setHydratorSection('event');
        $context->setUseHydratorCache(false);

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => Type::listOf($typeLoader(Entity\Artist::class, $context)),
                        'args' => [
                            'filter' => $filterLoader(Entity\Artist::class, $context),
                        ],
                        'resolve' => $resolveLoader(Entity\Artist::class, $context),
                    ],
                ],
            ]),
        ]);

        return $schema;
    }
}
