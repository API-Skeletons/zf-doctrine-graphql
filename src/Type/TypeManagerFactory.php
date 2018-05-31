<?php

namespace ZF\Doctrine\GraphQL\Type;

use Zend\Mvc\Service\AbstractPluginManagerFactory;

final class TypeManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = TypeManager::class;
}
