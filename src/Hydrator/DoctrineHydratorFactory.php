<?php

/**
 * This class is an edit of phpro/zf-doctrine-hydrator-module
 */

namespace ZF\Doctrine\GraphQL\Hydrator;

use Exception;
use Interop\Container\ContainerInterface;
use Zend\Hydrator\HydratorPluginManager;
use Zend\Hydrator\AbstractHydrator;
use Zend\Hydrator\Filter\FilterComposite;
use Zend\Hydrator\Filter\FilterInterface;
use Zend\Hydrator\FilterEnabledInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\NamingStrategy\NamingStrategyInterface;
use Zend\Hydrator\NamingStrategyEnabledInterface;
use Zend\Hydrator\Strategy\StrategyInterface;
use Zend\Hydrator\StrategyEnabledInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

/**
 * Class DoctrineHydratorFactory.
 */
class DoctrineHydratorFactory implements AbstractFactoryInterface
{
    const FACTORY_NAMESPACE = 'zf-doctrine-graphql-hydrator';

    /**
     * Cache of canCreateServiceWithName lookups.
     *
     * @var array
     */
    protected $lookupCache = [];

    /**
     * Determine if we can create a service with name.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     *
     * @return bool
     *
     * @throws ServiceNotFoundException
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (array_key_exists($requestedName, $this->lookupCache)) {
            return $this->lookupCache[$requestedName];
        }

        if (! $container->has('config')) {
            return false;
        }

        // Validate object is set
        $config = $container->get('config');
        $namespace = self::FACTORY_NAMESPACE;
        if (! isset($config[$namespace])
            || ! is_array($config[$namespace])
            || ! isset($config[$namespace][$requestedName])
        ) {
            $this->lookupCache[$requestedName] = false;

            return false;
        }

        $this->lookupCache[$requestedName] = true;

        return true;
    }

    /**
     * Determine if we can create a service with name. (v2)
     *
     * Provided for backwards compatiblity; proxies to canCreate().
     *
     * @param ServiceLocatorInterface $hydratorManager
     * @param string                  $name
     * @param string                  $requestedName
     *
     * @return bool
     *
     * @throws ServiceNotFoundException
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $hydratorManager, $name, $requestedName)
    {
        if (! $hydratorManager instanceof HydratorPluginManager) {
            throw new Exception('Invalid hydrator manager');
        }

        return $this->canCreate($hydratorManager->getServiceLocator(), $requestedName);
    }

    /**
     * Create and return the database-connected resource.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return DoctrineHydrator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $config = $config[self::FACTORY_NAMESPACE][$requestedName]['default'];

        $objectManager = $this->loadObjectManager($container, $config);

        $extractService = null;
        $hydrateService = null;

        $useEntityHydrator = (array_key_exists('use_generated_hydrator', $config) && $config['use_generated_hydrator']);
        $useCustomHydrator = (array_key_exists('hydrator', $config));

        if ($useEntityHydrator && $config['use_generated_hydrator']) {
            $hydrateService = $this->loadEntityHydrator($container, $config, $objectManager);
        }

        if ($useCustomHydrator && $config['hydrator']) {
            $extractService = $container->get($config['hydrator']);
            $hydrateService = $extractService;
        }

        # Use DoctrineModuleHydrator by default
        if (! isset($extractService, $hydrateService)) {
            $doctrineModuleHydrator = $this->loadDoctrineModuleHydrator($container, $config, $objectManager);
            $extractService = ($extractService ?: $doctrineModuleHydrator);
            $hydrateService = ($hydrateService ?: $doctrineModuleHydrator);
        }

        $this->configureHydrator($extractService, $container, $config, $objectManager);
        $this->configureHydrator($hydrateService, $container, $config, $objectManager);

        return new DoctrineHydrator($extractService, $hydrateService);
    }

    /**
     * Create and return the database-connected resource (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $hydratorManager
     * @param string                  $name
     * @param string                  $requestedName
     *
     * @return DoctrineHydrator
     */
    public function createServiceWithName(ServiceLocatorInterface $hydratorManager, $name, $requestedName)
    {
        if (! $hydratorManager instanceof HydratorPluginManager) {
            throw new Exception('Invalid hydrator manager');
        }

        return $this($hydratorManager->getServiceLocator(), $requestedName);
    }

    protected function getObjectManagerType($objectManager) : string
    {
        if (class_exists(EntityManager::class) && $objectManager instanceof EntityManager) {
            return 'ORM';
        }

        throw new ServiceNotCreatedException('Unknown object manager type: ' . get_class($objectManager));
    }

    /**
     * @param ContainerInterface $container
     * @param array              $config
     *
     * @return ObjectManager
     *
     * @throws ServiceNotCreatedException
     */
    protected function loadObjectManager(ContainerInterface $container, $config)
    {
        if (! $container->has($config['object_manager'])) {
            throw new ServiceNotCreatedException('The object_manager could not be found.');
        }

        return $container->get($config['object_manager']);
    }

    protected function loadEntityHydrator(ContainerInterface $container, $config, $objectManager)
    {
        $objectManagerType = $this->getObjectManagerType($objectManager);

        return;
    }

    /**
     * @param ContainerInterface $container
     * @param array              $config
     * @param ObjectManager      $objectManager
     *
     * @return HydratorInterface
     */
    protected function loadDoctrineModuleHydrator(ContainerInterface $container, $config, $objectManager)
    {
        $objectManagerType = $this->getObjectManagerType($objectManager);

        $hydrator = new DoctrineObject($objectManager, $config['by_value']);

        return $hydrator;
    }

    /**
     * @param AbstractHydrator   $hydrator
     * @param ContainerInterface $container
     * @param array              $config
     * @param ObjectManager      $objectManager
     *
     * @throws ServiceNotCreatedException
     */
    public function configureHydrator($hydrator, ContainerInterface $container, $config, $objectManager)
    {
        $this->configureHydratorFilters($hydrator, $container, $config, $objectManager);
        $this->configureHydratorStrategies($hydrator, $container, $config, $objectManager);
        $this->configureHydratorNamingStrategy($hydrator, $container, $config, $objectManager);
    }

    /**
     * @param AbstractHydrator   $hydrator
     * @param ContainerInterface $container
     * @param array              $config
     * @param ObjectManager      $objectManager
     *
     * @throws ServiceNotCreatedException
     */
    public function configureHydratorNamingStrategy($hydrator, ContainerInterface $container, $config, $objectManager)
    {
        if (! ($hydrator instanceof NamingStrategyEnabledInterface) || ! isset($config['naming_strategy'])) {
            return;
        }

        $namingStrategyKey = $config['naming_strategy'];
        if (! $container->has($namingStrategyKey)) {
            throw new ServiceNotCreatedException(sprintf('Invalid naming strategy %s.', $namingStrategyKey));
        }

        $namingStrategy = $container->get($namingStrategyKey);
        if (! $namingStrategy instanceof NamingStrategyInterface) {
            throw new ServiceNotCreatedException(
                sprintf('Invalid naming strategy class %s', get_class($namingStrategy))
            );
        }

        // Attach object manager:
        if ($namingStrategy instanceof ObjectManagerAwareInterface) {
            $namingStrategy->setObjectManager($objectManager);
        }

        $hydrator->setNamingStrategy($namingStrategy);
    }

    /**
     * @param AbstractHydrator   $hydrator
     * @param ContainerInterface $container
     * @param array              $config
     * @param ObjectManager      $objectManager
     *
     * @throws ServiceNotCreatedException
     */
    protected function configureHydratorStrategies($hydrator, ContainerInterface $container, $config, $objectManager)
    {
        if (! $hydrator instanceof StrategyEnabledInterface
            || ! isset($config['strategies'])
            || ! is_array($config['strategies'])
        ) {
            return;
        }

        foreach ($config['strategies'] as $field => $strategyKey) {
            if (! $container->has($strategyKey)) {
                throw new ServiceNotCreatedException(sprintf('Invalid strategy %s for field %s', $strategyKey, $field));
            }

            $strategy = $container->get($strategyKey);
            if (! $strategy instanceof StrategyInterface) {
                throw new ServiceNotCreatedException(
                    sprintf('Invalid strategy class %s for field %s', get_class($strategy), $field)
                );
            }

            // Attach object manager:
            if ($strategy instanceof ObjectManagerAwareInterface) {
                $strategy->setObjectManager($objectManager);
            }

            $hydrator->addStrategy($field, $strategy);
        }
    }

    /**
     * Add filters to the Hydrator based on a predefined configuration format, if specified.
     *
     * @param AbstractHydrator   $hydrator
     * @param ContainerInterface $container
     * @param array              $config
     * @param ObjectManager      $objectManager
     *
     * @throws ServiceNotCreatedException
     */
    protected function configureHydratorFilters($hydrator, ContainerInterface $container, $config, $objectManager)
    {
        if (! $hydrator instanceof FilterEnabledInterface
            || ! isset($config['filters'])
            || ! is_array($config['filters'])
        ) {
            return;
        }

        foreach ($config['filters'] as $name => $filterConfig) {
            $conditionMap = [
                'and' => FilterComposite::CONDITION_AND,
                'or' => FilterComposite::CONDITION_OR,
            ];
            $condition = isset($filterConfig['condition']) ?
                            $conditionMap[$filterConfig['condition']] :
                            FilterComposite::CONDITION_OR;

            $filterService = $filterConfig['filter'];
            if (! $container->has($filterService)) {
                throw new ServiceNotCreatedException(
                    sprintf('Invalid filter %s for field %s: service does not exist', $filterService, $name)
                );
            }

            $filterService = $container->get($filterService);
            if (! $filterService instanceof FilterInterface) {
                throw new ServiceNotCreatedException(
                    sprintf('Filter service %s must implement FilterInterface', get_class($filterService))
                );
            }

            if ($filterService instanceof ObjectManagerAwareInterface) {
                $filterService->setObjectManager($objectManager);
            }
            $hydrator->addFilter($name, $filterService, $condition);
        }
    }
}
