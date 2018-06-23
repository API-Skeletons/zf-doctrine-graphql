<?php

namespace ZF\Doctrine\GraphQL\Hydrator;

interface HydratorExtractToolInterface
{
    public function extract($entity, string $hydratorAlias, $options);
    public function extractToCollection($entityArray, string $hydratorAlias, $options);
}
