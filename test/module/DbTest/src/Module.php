<?php

namespace DbTest;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements
    ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    public function getAutoloaderConfig()
    {
        return ['Zend\Loader\StandardAutoloader' => ['namespaces' => [
            __NAMESPACE__ => __DIR__,
        ]]];
    }
}
