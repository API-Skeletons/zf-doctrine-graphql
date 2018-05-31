<?php

namespace ZF\Doctrine\GraphQL\Type;

use Interop\Container\ContainerInterface;

class LoaderFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $typeManager = $container->get(TypeManager::class);

        return new Loader($typeManager);
    }
}
