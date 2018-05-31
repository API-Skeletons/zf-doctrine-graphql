<?php

declare(strict_types=1);

namespace ZF\Doctrine\GraphQL\Field;

use Exception as FieldResolverException;
use GraphQL\Type\Definition\ResolveInfo;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

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

    public function __construct($hydratorManager, array $config)
    {
        $this->hydratorManager = $hydratorManager;
        $this->config = $config;
    }

    public function __invoke($source, $args, $context, ResolveInfo $info)
    {
        $entityClassName = ClassUtils::getRealClass(get_class($source));
        $splObjectHash = spl_object_hash($source);
        if (isset($this->extractValues[$splObjectHash][$info->fieldName])) {
            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        foreach ($this->config['zf-rest'] as $controllerName => $restConfig) {
            if ($restConfig['entity_class'] == $entityClassName) {
                $listener = $restConfig['listener'];
                $hydratorAlias = $this->config['zf-apigility']['doctrine-connected'][$listener]['hydrator'];
                break;
            }
        }

        if (! isset($hydratorAlias)) {
            throw new FieldResolverException('Hydrator alias not found for class ' . $entityClassName);
        }

        $hydrator = $this->hydratorManager->get($hydratorAlias);
        $this->extractValues[$splObjectHash] = $hydrator->extract($source);

        return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
    }
}
