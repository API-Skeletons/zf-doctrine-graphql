<?php

namespace ZF\Doctrine\GraphQL\Resolve;

use ArrayObject;
use Closure;
use DateTime;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Doctrine\Utils;
use ZF\Doctrine\GraphQL\Type\TypeManager;

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

        foreach ($config['zf-doctrine-graphql-entity-manager'] as $ormAlias) {
            try {
                $objectManager = $container->get($ormAlias);
                $objectManager->getClassMetadata($requestedName);

                return true;
            } catch (MappingException $e) {
                continue;
            }
        }

        return false;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Closure
    {
        $config = $container->get('config');

        foreach ($config['zf-rest'] as $controllerName => $restConfig) {
            if ($restConfig['entity_class'] == $requestedName) {
                $listener = $restConfig['listener'];
                $objectManagerAlias = $config['zf-apigility']['doctrine-connected'][$listener]['object_manager'];
                break;
            }
        }

        if (! $objectManagerAlias) {
            throw new Exception("Rest config not found for entity ${requestedName}");
        }

        $objectManager = $container->get($objectManagerAlias);

        return function($obj, $args, $context) use ($objectManager, $requestedName) {

            $id = $args['id'] ?? 0;

            $find = $objectManager
                ->getRepository($requestedName)
                ->find($id)
                ;

            return $find;
        };
    }
}
