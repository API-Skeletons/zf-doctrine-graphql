<?php

namespace ZF\Doctrine\GraphQL\Documentation;

use Interop\Container\ContainerInterface;

class HydratorConfigurationDocumentationProviderFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $config = $container->get('config');

        return new HydratorConfigurationDocumentationProvider($config);
    }
}
