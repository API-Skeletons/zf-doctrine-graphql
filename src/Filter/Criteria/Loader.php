<?php

namespace ZF\Doctrine\GraphQL\Filter\Criteria;

class Loader
{
    protected $filterManager;

    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    public function __invoke(string $name) : FilterType
    {
        return $this->filterManager->get($name);
    }
}
