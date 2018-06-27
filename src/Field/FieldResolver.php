<?php

declare(strict_types=1);

namespace ZF\Doctrine\GraphQL\Field;

use Exception as FieldResolverException;
use Zend\Hydrator\HydratorPluginManager;
use Doctrine\Common\Util\ClassUtils;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Executor\Executor;
use ZF\Doctrine\GraphQL\Context;
use ZF\Doctrine\GraphQL\Hydrator\HydratorExtractToolInterface;

/**
 * A field resolver which uses the Doctrine hydrator. Can be used byReference or byValue.
 */
class FieldResolver
{
    private $hydratorExtractTool;
    private $hydratorManager;

    /**
     * Cache all hydrator extract operations based on spl object hash
     *
     * @var array
     */
    private $extractValues = [];

    public function __construct(
        HydratorExtractToolInterface $hydratorExtractTool,
        HydratorPluginManager $hydratorManager
    ) {
        $this->hydratorExtractTool = $hydratorExtractTool;
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

        // If the hydrator does not exist pass handling to default Executor field resolver
        if (! $this->hydratorManager->has($hydratorAlias)) {
            return Executor::defaultFieldResolver($source, $args, $context, $info);
        }

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

            $this->extractValues[$splObjectHash]
                = $this->hydratorExtractTool->extract($source, $hydratorAlias, $context);

            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        // Use full hydrator cache
        if (isset($this->extractValues[$splObjectHash][$info->fieldName])) {
            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        $this->extractValues[$splObjectHash] = $this->hydratorExtractTool->extract($source, $hydratorAlias, $context);

        return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
    }
}
