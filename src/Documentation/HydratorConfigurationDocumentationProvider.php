<?php

namespace ZF\Doctrine\GraphQL\Documentation;

class HydratorConfigurationDocumentationProvider implements
    DocumentationProviderInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getEntity($entityClassName, array $options)
    {
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $entityClassName);
        $config = $this->config['zf-doctrine-graphql-hydrator'][$hydratorAlias][$options['hydrator_section']] ?? null;

        return $config['documentation']['_entity'] ?? null;
    }

    public function getField($entityClassName, $fieldName, array $options)
    {
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $entityClassName);
        $config = $this->config['zf-doctrine-graphql-hydrator'][$hydratorAlias][$options['hydrator_section']] ?? null;

        return $config['documentation'][$fieldName] ?? null;
    }
}
