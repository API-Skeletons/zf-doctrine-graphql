<?php

/**
 * Copied from GraphQLTests\Doctrine\Blog\Types\DateTimeType
 */

declare(strict_types=1);

namespace ZF\Doctrine\GraphQL\Type;

use DateTime;
use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils;

final class DateTimeType extends ScalarType
{
    /**
     * @var string
     */
    public $description =
    'The `DateTime` scalar type represents datetime data.
The format for the DateTime is ISO-8601
e.g. 2004-02-12T15:19:21+00:00.';

    /**
     * @codeCoverageIgnore
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (! $valueNode instanceof StringValueNode) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        return $valueNode->value;
    }

    /**
     * @codeCoverageIgnore
     */
    public function parseValue($value)
    {
        if (! is_string($value)) {
            $stringValue = print_r($value, true);
            throw new \UnexpectedValueException('Date is not a string: ' . $stringValue);
        }

        return DateTime::createFromFormat('Y-m-d\TH:i:sP', $value);
    }

    public function serialize($value)
    {
        if ($value instanceof DateTime) {
            $value = $value->format('c');
        }

        return $value;
    }
}
