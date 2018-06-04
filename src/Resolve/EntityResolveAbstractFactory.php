<?php

namespace ZF\Doctrine\GraphQL\Resolve;

use Closure;
use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Lexer;
use PhpMyAdmin\SqlParser\Token;

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

        if (! $hydratorConfig) {
            throw new Exception("Hydrator configuration not found for entity ${requestedName}");
        }

        $objectManager = $container->get($hydratorConfig['object_manager']);

        return function ($obj, $args, $context) use ($objectManager, $requestedName) {

            $queryBuilder = $objectManager->createQueryBuilder();
            $queryBuilder
                ->select('row')
                ->from($requestedName, 'row')
                ;
            $filter = $args['filter'] ?? [];

            foreach ($filter as $field => $value) {
                switch ($field) {
                    case '_neq':
                        die('neq hit');
                    default:
                        $valueParameter = md5(rand());
                        $queryBuilder->andWhere($queryBuilder->expr()->eq('row.' . $field, ":$valueParameter"));
                        $queryBuilder->setParameter($valueParameter, $value);

                        break;
                }
            }

            $id = $args['id'] ?? 0;
            if ($id) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq('row.id', $id)); // FIXME:  Account for non-id primary keys
            }

            return $queryBuilder->getQuery()->getResult();
        };
    }
}
