<?php

namespace ZF\Doctrine\GraphQL\Resolve;

use Closure;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Doctrine\QueryBuilder\Filter\Service\ORMFilterManager;

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

    /**
     * Loop through all configured ORM managers and if the passed $requestedName
     * as entity name is managed by the ORM return true;
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);

        return isset($config['zf-doctrine-graphql-hydrator'][$hydratorAlias]);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Closure
    {
        $config = $container->get('config');
        $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);
        $hydratorConfig = $config['zf-doctrine-graphql-hydrator'][$hydratorAlias] ?? null;
        $filterManager = $container->get(ORMFilterManager::class);

        if (! $hydratorConfig) {
            throw new Exception("Hydrator configuration not found for entity ${requestedName}");
        }

        $objectManager = $container->get($hydratorConfig['object_manager']);

        return function ($obj, $args, $context) use ($objectManager, $requestedName, $filterManager) {

            $queryBuilder = $objectManager->createQueryBuilder();
            $queryBuilder
                ->select('row')
                ->from($requestedName, 'row')
                ;
            $filter = $args['filter'] ?? [];

            $filterArray = [];
            $debugQuery = false;
            foreach ($filter as $field => $value) {
                if ($field == '_debug') {
                    $debugQuery = $value['value'];
                    continue;
                }

                if (strstr($field, '_')) {
                    $field = strtok($field, '_');
                    $filter = strtok('_');

                    $value['type'] = $filter;
                    $value['field'] = $field;
                    $filterArray[] = $value;
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
            if ($filterArray) {
                $metadata = $objectManager->getClassMetadata($requestedName);
                $filterManager->filter(
                    $queryBuilder,
                    $metadata,
                    $filterArray
                );
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
