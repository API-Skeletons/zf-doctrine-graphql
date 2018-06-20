<?php

declare(strict_types=1);

namespace ZF\Doctrine\GraphQL\Field;

use Exception as FieldResolverException;
use Zend\Hydrator\HydratorPluginManager;
use Doctrine\Common\Util\ClassUtils;
use GraphQL\Type\Definition\ResolveInfo;
use ZF\Doctrine\GraphQL\Context;

/**
 * A field resolver which uses the Doctrine hydrator. Can be used byReference or byValue.
 */
class FieldResolver
{
    private $hydratorManager;

    /**
     * Cache all hydrator extract operations based on spl object hash
     *
     * @var array
     */
    private $extractValues = [];

    public function __construct(HydratorPluginManager $hydratorManager)
    {
        $this->hydratorManager = $hydratorManager;
    }

    public function __invoke($source, $args, Context $context, ResolveInfo $info)
    {
        if (is_array($source)) {
            return $source[$info->fieldName];
        }

        $entityClassName = ClassUtils::getRealClass(get_class($source));
        $splObjectHash = spl_object_hash($source);
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $entityClassName);

        /**
         * For disabled hydrator cache store only last hydrator result and reuse for consecutive calls
         * then drop the cache if it doesn't hit.
         */
        if (! $context->getUseHydratorCache()) {
            if (isset($this->extractValues[$splObjectHash])) {
                return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
            } else {
                $this->extractValues = [];
            }

            $hydrator = $this->hydratorManager->get($hydratorAlias);
            $this->extractValues[$splObjectHash] = $hydrator->extract($source);

            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        // Use full hydrator cache
        if (isset($this->extractValues[$splObjectHash][$info->fieldName])) {
            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        $hydrator = $this->hydratorManager->get($hydratorAlias);
        $this->extractValues[$splObjectHash] = $hydrator->extract($source);

        return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
    }
}
