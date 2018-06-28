<?php

namespace ZF\Doctrine\GraphQL\Documentation;

interface DocumentationProviderInterface
{
    public function getField($entityName, $fieldName);
    public function getEntity($entityName);
}
