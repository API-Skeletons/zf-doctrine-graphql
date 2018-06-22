<?php

namespace ZF\Doctrine\GraphQL;

use Exception;
use GraphQL\Type\Definition\Type;

/**
 * Enable caching of build() resources
 */
abstract class AbstractAbstractFactory
{
    private $services = [];

    protected function isCached($requestedName, array $options = null)
    {
        foreach ($this->services as $service) {
            if ($service['requestedName'] == $requestedName && $service['options'] == $options) {
                return true;
            }
        }

        return false;
    }

    protected function getCache($requestedName, array $options = null)
    {
        foreach ($this->services as $service) {
            if ($service['requestedName'] == $requestedName && $service['options'] == $options) {
                return $service['instance'];
            }
        }

        throw new Exception('Cache not found for ' . $requestedName);
    }

    protected function cache($requestedName, array $options = null, $instance)
    {
        foreach ($this->services as $service) {
            if ($service['requestedName'] == $requestedName && $service['options'] == $options) {
                return;
            }
        }

        $this->services[] = [
            'requestedName' => $requestedName,
            'options' => $options,
            'instance' => $instance,
        ];

        return $instance;
    }

    protected function mapFieldType(string $fieldType)
    {
        switch ($fieldType) {
            case 'tinyint':
            case 'smallint':
            case 'integer':
            case 'int':
            case 'bigint':
                $graphQLType = Type::int();
                break;
            case 'boolean':
                $graphQLType = Type::boolean();
                break;
            case 'decimal':
            case 'float':
                $graphQLType = Type::float();
                break;
            case 'string':
            case 'text':
                $graphQLType = Type::string();
                break;
            case 'datetime':
                $graphQLType = Type::string();
                break;
            case 'array':
                $graphQLType = Type::listOf(Type::string());
                break;
            default:
                // Do not process unknown for now
                $graphQLType = null;
                break;
        }

        return $graphQLType;
    }
}
