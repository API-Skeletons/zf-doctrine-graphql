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
        $hydratorConfig = $config['zf-doctrine-graphql-hydrator'][$hydratorAlias];
        $filterManager = $container->get(ORMFilterManager::class);
        $orderByManager = $container->get(ORMOrderByManager::class);
        $queryProviderManager = $container->get(QueryProviderManager::class);
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
            if (! $queryProviderManager->has($requestedName)) {
                throw new Exception('Missing query provider for ' . $requestedName);
            }
            $queryProvider = $queryProviderManager->get($requestedName);
            $queryBuilder = $queryProvider->createQuery($objectManager);

            // Resolve top level filters
            $filter = $args['filter'] ?? [];
            $filterArray = [];
            $orderByArray = [];
            $skip = 0;
            $limit = $config['zf-doctrine-graphql']['limit'];
            foreach ($filter as $field => $value) {

                // Command fields
                if ($field == '_skip') {
                    $skip = $value;
                    continue;
                }

                if ($field == '_limit') {
                    if ($value <= $config['zf-doctrine-graphql']['limit']) {
                        $limit = $value;
                    }
                    continue;
                }

                // Handle most fields as $field_$type: $value
                if (! strstr($field, '_')) {
                    // Handle field:value
                     $filterArray[] = [
                        'type' => 'eq',
                        'field' => $field,
                        'value' => $value,
                    ];
                } else {
                    $field = strtok($field, '_');
                    $filter = strtok('_');

                    switch ($filter) {
                        case 'sort':
                            $orderByArray[] = [
                                'type' => 'field',
                                'field' => $field,
                                'direction' => $value,
                            ];
                            break;
                        case 'contains':
                            $filterArray[] = [
                                'type' => 'like',
                                'field' => $field,
                                'value' => '%' . $value . '%',
                            ];
                            break;
                        case 'startswith':
                            $filterArray[] = [
                                'type' => 'like',
                                'field' => $field,
                                'value' => $value . '%',
                            ];
                            break;
                        case 'endswith':
                            $filterArray[] = [
                                'type' => 'like',
                                'field' => $field,
                                'value' => '%' . $value,
                            ];
                            break;
                        case 'between':
                            $value['type'] = $filter;
                            $value['field'] = $field;
                            $filterArray[] = $value;
                            break;
                        case 'in':
                            $filterArray[] = [
                                'type' => 'in',
                                'field' => $field,
                                'values' => $value,
                            ];
                            break;
                        case 'notin':
                            $filterArray[] = [
                                'type' => 'notin',
                                'field' => $field,
                                'values' => $value,
                            ];
                            break;
                        case 'isnull':
                            if ($value === true) {
                                $filterArray[] = [
                                    'type' => 'isnull',
                                    'field' => $field,
                                    'values' => null,
                                ];
                            } else {
                                $filterArray[] = [
                                    'type' => 'isnotnull',
                                    'field' => $field,
                                    'values' => null,
                                ];
                            }
                            break;
                        default:
                            $filterArray[] = [
                                'type' => $filter,
                                'field' => $field,
                                'value' => $value,
                            ];
                            break;
                    }
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
                $queryBuilder->setMaxResults($limit);
            }

            return $queryBuilder->getQuery()->getResult();
        };
    }
}
