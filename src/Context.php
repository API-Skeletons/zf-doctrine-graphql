<?php

namespace ZF\Doctrine\GraphQL;

use Zend\Stdlib\AbstractOptions;

/**
 * This class serves as the context array for
 * GraphQL.  Default values are set here
 */
class Context extends AbstractOptions
{
    protected $hydratorSection;
    protected $limit;
    protected $useHydratorCache;

    public function __construct($options = null)
    {
        parent::__construct($options);

        if (! $this->getHydratorSection()) {
            $this->setHydratorSection('default');
        }

        if (! $this->getLimit()) {
            $this->setLimit(1000);
        }

        if ($this->getUseHydratorCache() !== false) {
            $this->setUseHydratorCache(true);
        }
    }

    public function setHydratorSection(string $value)
    {
        $this->hydratorSection = $value;

        return $this;
    }

    public function getHydratorSection()
    {
        return $this->hydratorSection;
    }

    public function setLimit(int $value)
    {
        $this->limit = $value;

        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getUseHydratorCache()
    {
        return $this->useHydratorCache;
    }

    public function setUseHydratorCache(bool $value)
    {
        $this->useHydratorCache = $value;

        return $this;
    }
}
