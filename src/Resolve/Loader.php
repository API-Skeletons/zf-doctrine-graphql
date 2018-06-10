<?php

namespace ZF\Doctrine\GraphQL\Resolve;

class Loader
{
    protected $resolveManager;

    public function __construct(ResolveManager $resolveManager)
    {
        $this->resolveManager = $resolveManager;
    }

    public function __invoke(string $name)
    {
        return $this->resolveManager->get($name);
    }
}
