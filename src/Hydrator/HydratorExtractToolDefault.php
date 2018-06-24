<?php

namespace ZF\Doctrine\GraphQL\Hydrator;

use Zend\Hydrator\HydratorPluginManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Instantiator\Instantiator;
use ZF\Doctrine\GraphQL\Context;

/**
 * This tool centralizes all extract operations for the module.
 * By doing so caching and optimization of operations can be applied
 * by overriding the this class alias in the config.
 */
class HydratorExtractToolDefault implements
    HydratorExtractToolInterface
{
    protected $hydratorManager;

    public function __construct(HydratorPluginManager $hydratorManager)
    {
        $this->hydratorManager = $hydratorManager;
    }

    // Extract an array of entities and return a collection
    public function extractToCollection($entityArray, string $hydratorAlias, $options)
    {
        $options = $this->optionsToArray($options);
        $hydrator = $this->hydratorManager->build($hydratorAlias, $options);

        $resultCollection = new ArrayCollection();
        foreach ($entityArray as $value) {
            // @codeCoverageIgnoreStart
            if (is_array($value)) {
                $resultCollection->add($value);
            // @codeCoverageIgnoreEnd
            } else {
                $resultCollection->add($hydrator->extract($value));
            }
        }

        return $resultCollection;
    }

    // Extract a single entity
    public function extract($entity, string $hydratorAlias, $options)
    {
        // @codeCoverageIgnoreStart
        if (is_array($entity)) {
            return $entity;
        }
        // @codeCoverageIgnoreEnd

        $options = $this->optionsToArray($options);
        $hydrator = $this->hydratorManager->build($hydratorAlias, $options);

        return $hydrator->extract($entity);
    }

    public function getFieldArray(string $entityClassName, string $hydratorAlias, $options)
    {
        $instantiator = new Instantiator();
        $entity = $instantiator->instantiate($entityClassName);

        $options = $this->optionsToArray($options);
        $hydrator = $this->hydratorManager->build($hydratorAlias, $options);

        return array_keys($hydrator->extract($entity));
    }

    private function optionsToArray($options)
    {
        if ($options instanceof Context) {
            $options = $options->toArray();
        }

        return $options;
    }
}
