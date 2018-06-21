<?php

namespace ZF\Doctrine\GraphQL;

use Zend\Stdlib\AbstractOptions;

/**
 * This class serves as the context array for
 * GraphQL.  Default values are set here
 */
class Context extends AbstractOptions
{
    protected $hydratorSection = 'default';
    protected $limit = 1000;
    protected $useHydratorCache = false;

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
