<?php

namespace ZF\Doctrine\GraphQL\QueryProvider;

use Zend\Mvc\Service\AbstractPluginManagerFactory;

final class QueryProviderManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = QueryProviderManager::class;
}
