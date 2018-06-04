<?php

namespace ZF\Doctrine\GraphQL\Filter;

use Interop\Container\ContainerInterface;

class LoaderFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $filterManager = $container->get(FilterManager::class);

        return new Loader($filterManager);
    }
}
