<?php

namespace ZF\Doctrine\GraphQL\Criteria;

use Zend\Mvc\Service\AbstractPluginManagerFactory;

final class CriteriaManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = CriteriaManager::class;
}
