<?php

namespace ZF\Doctrine\GraphQL\Resolve;

use Closure;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Doctrine\QueryBuilder\Filter\Service\ORMFilterManager;
use ZF\Doctrine\QueryBuilder\OrderBy\Service\ORMOrderByManager;
use ZF\Doctrine\GraphQL\QueryProvider\QueryProviderManager;

final class EntityResolveAbstractFactory implements
    AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this->canCreate($services, $requestedName);
    }

    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this($services, $requestedName);
    }

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $hydratorManager = $container->get('HydratorManager');
        $queryProviderManager = $container->get(QueryProviderManager::class);
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);

        if (! $queryProviderManager->has($requestedName)) {
            throw new Exception('QueryProvider not found for ' . $requestedName);
        }

        if (! $hydratorManager->has($hydratorAlias)) {
            throw new Exception('Hydrator configuration not found for ' . $requestedName);
        }

        return $queryProviderManager->has($requestedName) && $hydratorManager->has($hydratorAlias);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Closure
    {
        $config = $container->get('config');
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);
        $hydratorConfig = $config['zf-doctrine-graphql-hydrator'][$hydratorAlias] ?? null;
        $filterManager = $container->get(ORMFilterManager::class);
        $orderByManager = $container->get(ORMOrderByManager::class);
        $queryProviderManager = $container->get(QueryProviderManager::class);

        if (! $hydratorConfig) {
            throw new Exception("Hydrator configuration not found for entity ${requestedName}");
        }

        $objectManager = $container->get($hydratorConfig['object_manager']);

        return function (
            $obj,
            $args,
            $context
        ) use (
            $config,
            $objectManager,
            $requestedName,
            $filterManager,
            $orderByManager,
            $queryProviderManager
        ) {
            // Build query builder from Query Provider
            $queryBuilder = $queryProviderManager->get($requestedName)->createQuery($objectManager);

            // Resolve top level filters
            $filter = $args['filter'] ?? [];
            $filterArray = [];
            $orderByArray = [];
            $debugQuery = false;
            $skip = 0;
            $limit = $config['zf-doctrine-graphql']['limit'];
            foreach ($filter as $field => $value) {
                if ($field == '_debug') {
                    $debugQuery = $value;
                    continue;
                }

                if ($field == '_skip') {
                    $skip = $value;
                    continue;
                }

                if ($field == '_limit') {
                    $limit = $value;
                    continue;
                }

                if (strstr($field, '_')) {
                    $field = strtok($field, '_');
                    $filter = strtok('_');

                    if ($filter == 'orderby') {
                        $orderByArray[] = [
                            'type' => 'field',
                            'field' => $field,
                            'direction' => $value,
                        ];
                    } else {
                        $value['type'] = $filter;
                        $value['field'] = $field;
                        $filterArray[] = $value;
                    }
                } else {
                    $filterArray[] = [
                        'type' => 'eq',
                        'field' => $field,
                        'value' => $value,
                        'where' => 'and',
                        'format' => 'Y-m-d\TH:i:sP',
                    ];
                }
            }

            // Process fitlers through filter manager
            $metadata = $objectManager->getClassMetadata($requestedName);
            if ($filterArray) {
                $filterManager->filter(
                    $queryBuilder,
                    $metadata,
                    $filterArray
                );
            }
            if ($orderByArray) {
                $orderByManager->orderBy(
                    $queryBuilder,
                    $metadata,
                    $orderByArray
                );
            }
            if ($skip) {
                $queryBuilder->setFirstResult($skip);
            }
            if ($limit) {
                if ($config['zf-doctrine-graphql']['limit'] < $limit) {
                    $limit = $config['zf-doctrine-graphql']['limit'];
                }
                $queryBuilder->setMaxResults($limit);
            }

            if ($debugQuery) {
                print_r($queryBuilder->getQuery()->getDql());
                print_r($queryBuilder->getQuery()->getParameters());
                die();
            }

            return $queryBuilder->getQuery()->getResult();
        };
    }
}
