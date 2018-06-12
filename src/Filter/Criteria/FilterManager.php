<?php

namespace ZF\Doctrine\GraphQL\Filter\Criteria;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;
use GraphQL\Type\Definition\Type;

class FilterManager extends AbstractPluginManager
{
    /**
     * @var string
     */
    protected $instanceOf = Type::class;

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against `$instanceOf`.
     *
     * @param mixed $instance
     * @throws Exception\InvalidServiceException
     * @codeCoverageIgnore
     */
    public function validate($instance)
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new Exception\InvalidServiceException(sprintf(
                '%s can only create instances of %s; %s is invalid',
                get_class($this),
                $this->instanceOf,
                is_object($instance) ? get_class($instance) : gettype($instance)
            ));
        }
    }

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException
     * @codeCoverageIgnore
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (Exception\InvalidServiceException $e) {
            throw new Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
