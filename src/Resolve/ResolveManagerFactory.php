<?php

namespace ZF\Doctrine\GraphQL\Resolve;

use Zend\Mvc\Service\AbstractPluginManagerFactory;

final class ResolveManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = ResolveManager::class;
}
