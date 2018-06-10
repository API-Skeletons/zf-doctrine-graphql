<?php

/**
 * This class is an edit of phpro/zf-doctrine-hydrator-module
 */

namespace ZF\Doctrine\GraphQL\Hydrator;

use Zend\Hydrator\HydratorInterface;

/**
 * Class DoctrineHydrator.
 */
class DoctrineHydrator implements HydratorInterface
{
    /**
     * @var HydratorInterface
     */
    protected $extractService;

    /**
     * @var HydratorInterface
     */
    protected $hydrateService;

    public function __construct($extractService, $hydrateService)
    {
        $this->extractService = $extractService;
        $this->hydrateService = $hydrateService;
    }

    /**
     * @return \Zend\Hydrator\HydratorInterface
     */
    public function getExtractService()
    {
        return $this->extractService;
    }

    /**
     * @return \Zend\Hydrator\HydratorInterface
     */
    public function getHydrateService()
    {
        return $this->hydrateService;
    }

    /**
     * Extract values from an object.
     *
     * @param object $object
     *
     * @return array
     */
    public function extract($object)
    {
        return $this->extractService->extract($object);
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param array $data
     * @param object $object
     *
     * @return object
     */
    public function hydrate(array $data, $object)
    {
        // Zend hydrator:
        if ($this->hydrateService instanceof HydratorInterface) {
            return $this->hydrateService->hydrate($data, $object);
        }

        // Doctrine hydrator: (parameters switched)
        return $this->hydrateService->hydrate($object, $data);
    }
}
