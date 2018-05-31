<?php

namespace ZF\Doctrine\GraphQL\Console;

use Exception;
use Interop\Container\ContainerInterface;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Zend\Config\Config;
use Zend\Config\Writer\PhpArray;

final class HydratorConfigurationSkeletonController extends AbstractConsoleController implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function indexAction()
    {
         $objectManagerAlias = $this->params()->fromRoute('object-manager') ?? 'doctrine.entitymanager.orm_default';

         if (! $this->container->has($objectManagerAlias)) {
             throw new Exception('Invalid object manager alias');
         }
         $objectManager = $this->container->get($objectManagerAlias);

        $metadata = $objectManager->getMetadataFactory()->getAllMetadata();

        $config = ['zf-doctrine-graphql-hydrator' => []];
        foreach ($metadata as $classMetadata) {
            $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $classMetadata->getName());

            $config['zf-doctrine-graphql-hydrator'][$hydratorAlias] = [
                'entity_class' => $classMetadata->getName(),
                'object_manager' => $objectManagerAlias,
                'by_value' => true,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => [],
                'filters' => [],
            ];
        }

        $configObject = new Config($config);
        $writer = new PhpArray();

        echo $writer->toString($configObject);
    }
}
