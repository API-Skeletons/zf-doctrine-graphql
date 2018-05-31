<?php

namespace ZF\Doctrine\GraphQL\Resolve;

use Interop\Container\ContainerInterface;

class LoaderFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $resolveManager = $container->get(ResolveManager::class);

        return new Loader($resolveManager);
    }
}
