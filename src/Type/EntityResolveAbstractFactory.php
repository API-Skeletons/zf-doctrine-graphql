<?php

namespace ZF\Doctrine\GraphQL\Type;

use ArrayObject;
use DateTime;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Doctrine\Utils;

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

        foreach ($config['graphql-doctrine-entity-manager'] as $ormAlias) {
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

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : EntityType
    {
        $config = $container->get('config');
        $hydratorManager = $container->get('HydratorManager');

        foreach ($config['zf-rest'] as $controllerName => $restConfig) {
            if ($restConfig['entity_class'] == $requestedName) {
                $name = $restConfig['service_name'];
                $listener = $restConfig['listener'];
                $hydratorAlias = $config['zf-apigility']['doctrine-connected'][$listener]['hydrator'];
                $objectManagerAlias = $config['zf-apigility']['doctrine-connected'][$listener]['object_manager'];
                break;
            }
        }

        if (! $name) {
            throw new Exception("Rest config not found for entity ${requestedName}");
        }

        $objectManager = $container->get($objectManagerAlias);

        return function($obj, $args, $context) use ($objectManager, $requestedName) {
            die('resolving');
            $find = $objectManager
                ->getRepository($requestedName)
                ->find(1)
                ;

            return $find;
        };
    }
}
