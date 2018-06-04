<?php

namespace ZF\Doctrine\GraphQL\Filter;

use Zend\Mvc\Service\AbstractPluginManagerFactory;

final class FilterManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = FilterManager::class;
}
