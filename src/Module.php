<?php

namespace ZF\Doctrine\GraphQL;

use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\ModuleManager;
use GraphQL\Type\Definition\ObjectType;

class Module implements
    BootstrapListenerInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function init(ModuleManager $moduleManager)
    {
        $sm = $moduleManager->getEvent()->getParam('ServiceManager');
        $serviceListener = $sm->get('ServiceListener');

        $serviceListener->addServiceManager(
            Doctrine\Type\TypeManager::class,
            'graphql-doctrine-type',
            ObjectType::class,
            'getGraphQLDoctrineTypeConfig'
        );

        $serviceListener->addServiceManager(
            Doctrine\Resolve\ResolveManager::class,
            'graphql-doctrine-resolve',
            'function',
            'getGraphQLDoctrineTypeConfig'
        );
    }

    public function onBootstrap(EventInterface $event)
    {
        $fieldResolver = $event->getParam('application')
            ->getServiceManager()
            ->get(Field\FieldResolver::class)
            ;

        GraphQL::setDefaultFieldResolver($fieldResolver);
    }
}
