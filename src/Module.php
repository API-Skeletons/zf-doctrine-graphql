<?php

namespace ZF\Doctrine\GraphQL;

use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\ModuleManager;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\GraphQL;

class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface
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
            Type\TypeManager::class,
            'zf-doctrine-graphql-type',
            ObjectType::class,
            'getZFDoctrineGraphQLTypeConfig'
        );

        $serviceListener->addServiceManager(
            Resolve\ResolveManager::class,
            'zf-doctrine-graphql-resolve',
            'function',
            'getZFDoctrineGraphQLResolveConfig'
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
