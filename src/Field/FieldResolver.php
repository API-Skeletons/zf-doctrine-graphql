<?php

declare(strict_types=1);

namespace ZF\Doctrine\GraphQL\Field;

use Exception as FieldResolverException;
use Zend\Hydrator\HydratorPluginManager;
use GraphQL\Type\Definition\ResolveInfo;
use Doctrine\Common\Util\ClassUtils;

/**
 * A field resolver which uses the Doctrine hydrator. Can be used byReference or byValue.
 */
class FieldResolver
{
    private $hydratorManager;
    private $config;

    /**
     * Cache all hydrator extract operations based on spl object hash
     *
     * @var array
     */
    private $extractValues = [];

    public function __construct(HydratorPluginManager $hydratorManager, array $config)
    {
        $this->hydratorManager = $hydratorManager;
        $this->config = $config;
    }

    public function __invoke($source, $args, $context, ResolveInfo $info)
    {
        if (is_array($source)) {
            return $source[$info->fieldName];
        }

        $entityClassName = ClassUtils::getRealClass(get_class($source));
        $splObjectHash = spl_object_hash($source);
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $entityClassName);

        // For disabled hydrator cache do not store hydrator result
        if (isset($this->config['use_hydrator_cache']) && ! $this->config['use_hydrator_cache']) {
            $hydrator = $this->hydratorManager->get($hydratorAlias);
            $data = $hydrator->extract($source);

            return $data[$info->fieldName] ?? null;
        }

        // Use hydrator cache
        if (isset($this->extractValues[$splObjectHash][$info->fieldName])) {
            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        $hydrator = $this->hydratorManager->get($hydratorAlias);
        $this->extractValues[$splObjectHash] = $hydrator->extract($source);

        return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
    }
}
