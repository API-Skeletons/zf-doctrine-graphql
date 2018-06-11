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
        $user = new Entity\User();
        $user->name = 'test1';
        $user->createdAt = new DateTime('2010-01-01');
        $objectManager->persist($user);
        $objectManager->flush();

    }

    protected function getObjectManager()
    {
        return $this->getApplication()
            ->getServiceManager()
            ->get('doctrine.entitymanager.orm_default')
            ;
    }

    protected function getSchema()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        $typeLoader = $serviceManager->get(TypeLoader::class);
        $filterLoader = $serviceManager->get(FilterLoader::class);
        $resolveLoader = $serviceManager->get(ResolveLoader::class);

        $schema = new Schema([
            'query' => new ObjectType([
                'name' => 'query',
                'fields' => [
                    'artist' => [
                        'type' => Type::listOf($typeLoader(Entity\Artist::class)),
                        'args' => [
                            'filter' => $filterLoader(Entity\Artist::class),
                        ],
                        'resolve' => $resolveLoader(Entity\Artist::class),
                    ],
                    'performance' => [
                        'type' => Type::listOf($typeLoader(Entity\Performance::class)),
                        'args' => [
                            'filter' => $filterLoader(Entity\Performance::class),
                        ],
                        'resolve' => $resolveLoader(Entity\Performance::class),
                    ],
                    'user' => [
                        'type' => Type::listOf($typeLoader(Entity\User::class)),
                        'args' => [
                            'filter' => $filterLoader(Entity\User::class),
                        ],
                        'resolve' => $resolveLoader(Entity\User::class),
                    ],
                    'address' => [
                        'type' => Type::listOf($typeLoader(Entity\Address::class)),
                        'args' => [
                            'filter' => $filterLoader(Entity\Address::class),
                        ],
                        'resolve' => $resolveLoader(Entity\Address::class),
                    ],
                ],
            ]),
        ]);

        return $schema;
    }
}
