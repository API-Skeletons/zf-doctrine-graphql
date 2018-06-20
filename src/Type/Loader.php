<?php

namespace ZF\Doctrine\GraphQL\Type;

use ZF\Doctrine\GraphQL\Context;

class Loader
{
    protected $typeManager;

    public function __construct(TypeManager $typeManager)
    {
        $this->typeManager = $typeManager;
    }

    public function __invoke(string $name, Context $context = null) : EntityType
    {
        $context = $context ?? new Context();

        return $this->typeManager->build($name, $context->toArray());
    }
}
