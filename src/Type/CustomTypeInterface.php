<?php

/**
 * To use GraphQL with a custom Doctrine type
 * implement this interface on the custom doctrine Type
 */

namespace ZF\Doctrine\GraphQL\Type;

interface CustomTypeInterface
{
    public function mapGraphQLFieldType();
}
