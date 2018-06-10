<?php

namespace ZF\Doctrine\GraphQL;

use Exception;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\ModuleManager;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\GraphQL;

class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface,
    InitProviderInterface,
    ConsoleUsageProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getConsoleUsage(Console $console)
    {
        return [
            'graphql:config-skeleton [--object-manager=]' => 'Create GraphQL configuration skeleton',
        ];
    }

    public function init(ModuleManagerInterface $manager)
    {
        if (! $manager instanceof ModuleManager) {
            throw new Exception('Invalid module manager');
        }

        $sm = $manager->getEvent()->getParam('ServiceManager');
        $serviceListener = $sm->get('ServiceListener');

        $serviceListener->addServiceManager(
            QueryProvider\QueryProviderManager::class,
            'zf-doctrine-graphql-query-provider',
            QueryProvider\QueryProviderInterface::class,
            'getZFDoctrineGraphQLQueryProviderConfig'
        );

        $serviceListener->addServiceManager(
            Type\TypeManager::class,
            'zf-doctrine-graphql-type',
            ObjectType::class,
            'getZFDoctrineGraphQLTypeConfig'
        );

        $serviceListener->addServiceManager(
            Filter\FilterManager::class,
            'zf-doctrine-graphql-filter',
            InputObjectType::class,
            'getZFDoctrineGraphQLFilterConfig'
        );

        $serviceListener->addServiceManager(
            Filter\Criteria\FilterManager::class,
            'zf-doctrine-graphql-filter-criteria',
            InputObjectType::class,
            'getZFDoctrineGraphQLFilterCriteriaConfig'
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
