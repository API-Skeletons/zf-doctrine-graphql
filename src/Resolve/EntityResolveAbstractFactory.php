<?php

namespace ZF\Doctrine\GraphQL\Resolve;

use Closure;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\SharedEventManagerInterface;
use ZF\Doctrine\QueryBuilder\Filter\Service\ORMFilterManager;
use ZF\Doctrine\QueryBuilder\OrderBy\Service\ORMOrderByManager;

final class EntityResolveAbstractFactory implements
    AbstractFactoryInterface
{
    const FILTER_QUERY_BUILDER = 'filterQueryBuilder';

    protected $events;

    private function createEventManager(SharedEventManagerInterface $sharedEventManager)
    {
        $this->events = new EventManager(
            $sharedEventManager,
            [
                __CLASS__,
                get_class($this)
            ]
        );

        return $this->events;
    }

    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * @codeCoverageIgnore
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this->canCreate($services, $requestedName);
    }

    /**
     * @codeCoverageIgnore
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this($services, $requestedName);
    }

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $hydratorManager = $container->get('HydratorManager');
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);

        return $hydratorManager->has($hydratorAlias);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Closure
    {
        // Setup Events
        $this->createEventManager($container->get('SharedEventManager'));

        $config = $container->get('config');
        $hydratorManager = $container->get('HydratorManager');
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);
        $hydrator = $hydratorManager->get($hydratorAlias);
        $hydratorConfig = $config['zf-doctrine-graphql-hydrator'][$hydratorAlias];
        $filterManager = $container->get(ORMFilterManager::class);
        $orderByManager = $container->get(ORMOrderByManager::class);
        $objectManager = $container->get($hydratorConfig['object_manager']);

        return function (
            $obj,
            $args,
            $context
        ) use (
            $config,
            $hydrator,
            $objectManager,
            $requestedName,
            $filterManager,
            $orderByManager
        ) {

            // Build query builder from Query Provider
            $queryBuilder = ($objectManager->createQueryBuilder())
                ->select('row')
                ->from($requestedName, 'row')
                ;
            $this->getEventManager()->trigger(
                self::FILTER_QUERY_BUILDER,
                $this,
                [
                    'objectManager' => $objectManager,
                    'queryBuilder' => $queryBuilder,
                    'entityClassName' => $requestedName,
                ]
            );

            // Resolve top level filters
            $filter = $args['filter'] ?? [];
            $filterArray = [];
            $orderByArray = [];
            $distinctField = null;
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
                        case 'distinct':
                            if (! $distinctField && $value) {
                                $distinctField = $field;
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
                foreach ($filterArray as $key => $filter) {
                    $filterArray[$key]['format'] = 'Y-m-d\TH:i:sP';
                }

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

            $results = $queryBuilder->getQuery()->getResult();

            $matching = [];
            foreach ($results as $key => $value) {
                $matching[$key] = $hydrator->extract($value);
            }

            if ($distinctField) {
                $distinctValueArray = [];
                foreach ($matching as $key => $value) {
                    if (! in_array($value[$distinctField], $distinctValueArray)) {
                        $distinctValueArray[] = $value[$distinctField];
                    } else {
                        unset($matching[$key]);
                    }
                }
            }

            return $matching;
        };
    }
}
