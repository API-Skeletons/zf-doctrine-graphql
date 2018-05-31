<?php

namespace ZF\Doctrine\GraphQL\Type;

class Loader
{
    protected $typeManager;

    public function __construct(TypeManager $typeManager)
    {
        $this->typeManager = $typeManager;
    }

    public function __invoke(string $name) : EntityType
    {
        return $this->typeManager->get($name);
    }
}
