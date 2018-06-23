<?php

namespace ZF\Doctrine\GraphQL\Field;

use Interop\Container\ContainerInterface;

class FieldResolverFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $hydratorExtractTool = $container->get('ZF\\Doctrine\\GraphQL\\Hydrator\\HydratorExtractTool');

        return new FieldResolver($hydratorExtractTool);
    }
}
