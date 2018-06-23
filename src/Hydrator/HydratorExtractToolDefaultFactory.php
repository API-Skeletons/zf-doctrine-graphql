<?php

namespace ZF\Doctrine\GraphQL\Hydrator;

use Interop\Container\ContainerInterface;

class HydratorExtractToolDefaultFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $hydratorManager = $container->get('HydratorManager');

        return new HydratorExtractToolDefault($hydratorManager);
    }
}
