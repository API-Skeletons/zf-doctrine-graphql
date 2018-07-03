<?php

namespace ZF\Doctrine\GraphQL;

use Exception;
use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\SharedEventManagerInterface;
use GraphQL\Type\Definition\Type;

/**
 * Enable caching of build() resources
 */
abstract class AbstractAbstractFactory
{
    private $services = [];
    protected $events;

    protected function createEventManager(SharedEventManagerInterface $sharedEventManager)
    {
        $this->events = new EventManager(
            $sharedEventManager,
            [
                'ZF\\Doctrine\\GraphQL\\Event',
            ]
        );

        return $this->events;
    }

    public function getEventManager()
    {
        return $this->events;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // Setup Events
        $this->createEventManager($container->get('SharedEventManager'));
    }

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

        // @codeCoverageIgnoreStart
        throw new Exception('Cache not found for ' . $requestedName);
        // @codeCoverageIgnoreEnd
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
                // @codeCoverageIgnoreStart
                // Do not process unknown for now
                $graphQLType = null;
                break;
        }
                // @codeCoverageIgnoreEnd

        return $graphQLType;
    }

    /**
     * In order to support fields with underscores we need to know
     * if the possible filter name we found as the last _part of the
     * filter field name is indeed a filter else it could be a field
     *     e.g. id_name filter resolves to 'name' and is not a filter
     *     e.g. id_eq filter resolves to 'eq' and is a filter
     */
    public function isFilter($filterName)
    {
        switch (strtolower($filterName)) {
            case 'eq':
            case 'neq':
            case 'gt':
            case 'lt':
            case 'gte':
            case 'lte':
            case 'in':
            case 'notin':
            case 'between':
            case 'contains':
            case 'startswith':
            case 'endswith':
            case 'memberof':
            case 'isnull':
            case 'sort':
            case 'distinct':
                return true;
            // @codeCoverageIgnoreStart
            default:
                return false;
            // @codeCoverageIgnoreEnd
        }
    }
}
