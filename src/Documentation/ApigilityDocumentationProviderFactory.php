<?php

namespace ZF\Doctrine\GraphQL\Documentation;

use Interop\Container\ContainerInterface;

/**
 * @codeCoverageIgnore
 */
class ApigilityDocumentationProviderFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $config = $container->get('config');

        return new ApigilityDocumentationProvider($config);
    }
}
