<?php

namespace ZF\Doctrine\GraphQL;

use DateTime;
use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'zf-doctrine-criteria-filter' => $this->getDoctrineCriteriaFilterConfig(),
            'hydrators' => $this->getHydratorConfig(),
            'zf-doctrine-graphql-type' => $this->getDoctrineGraphQLTypeConfig(),
            'zf-doctrine-graphql-filter' => $this->getDoctrineGraphQLFilterConfig(),
            'zf-doctrine-graphql-criteria' => $this->getDoctrineGraphQLCriteriaConfig(),
            'zf-doctrine-graphql-resolve' => $this->getDoctrineGraphQLResolveConfig(),
            'controllers' => $this->getControllerConfig(),
            'console' => [
                'router' => $this->getConsoleRouterConfig(),
            ],
        ];
    }

    public function getDependencyConfig()
    {
        return [
            'factories' => [
                Hydrator\Filter\FilterDefault::class => InvokableFactory::class,
                Hydrator\Filter\Password::class => InvokableFactory::class,
                Hydrator\Strategy\ToBoolean::class => InvokableFactory::class,
                Hydrator\Strategy\ToFloat::class => InvokableFactory::class,
                Hydrator\Strategy\ToInteger::class => InvokableFactory::class,
                Hydrator\Strategy\NullifyOwningAssociation::class => InvokableFactory::class,
                Hydrator\Strategy\AssociationDefault::class => InvokableFactory::class,
                Hydrator\Strategy\FieldDefault::class => InvokableFactory::class,

                Criteria\Loader::class => Criteria\LoaderFactory::class,
                Criteria\FilterManager::class => Criteria\FilterManagerFactory::class,
                Field\FieldResolver::class => Field\FieldResolverFactory::class,
                Filter\Loader::class => Filter\LoaderFactory::class,
                Filter\FilterManager::class => Filter\FilterManagerFactory::class,
                Resolve\ResolveManager::class => Resolve\ResolveManagerFactory::class,
                Resolve\Loader::class => Resolve\LoaderFactory::class,
                Type\Loader::class => Type\LoaderFactory::class,
                Type\TypeManager::class => Type\TypeManagerFactory::class,
            ],
        ];
    }

    public function getDoctrineCriteriaFilterConfig()
    {
        return [
            'aliases' => [
                'between' => Filter\Criteria\Type\Between::class,
                'isnull' => Filter\Criteria\Type\IsNull::class,
                'isnotnull' => Filter\Criteria\Type\IsNotNull::class,
            ],
            'factories' => [
                Filter\Criteria\Type\Between::class => InvokableFactory::class,
                Filter\Criteria\Type\IsNull::class => InvokableFactory::class,
                Filter\Criteria\Type\IsNotNull::class => InvokableFactory::class,
            ],
        ];
    }

    public function getHydratorConfig()
    {
        return [
            'abstract_factories' => [
                Hydrator\DoctrineHydratorFactory::class,
            ],
        ];
    }

    public function getDoctrineGraphQLTypeConfig()
    {
        return [
            'invokables' => [
                DateTime::class => Type\DateTimeType::class,
            ],
            'abstract_factories' => [
                Type\EntityTypeAbstractFactory::class,
            ],
        ];
    }

    public function getDoctrineGraphQLFilterConfig()
    {
        return [
            'abstract_factories' => [
                Filter\FilterTypeAbstractFactory::class,
            ],
        ];
    }

    public function getDoctrineGraphQLCriteriaConfig()
    {
        return [
            'abstract_factories' => [
                Criteria\FilterTypeAbstractFactory::class,
            ],
        ];
    }

    public function getDoctrineGraphQLResolveConfig()
    {
        return [
            'abstract_factories' => [
                Resolve\EntityResolveAbstractFactory::class,
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Console\ConfigurationSkeletonController::class
                    => Console\ConfigurationSkeletonControllerFactory::class,
            ],
        ];
    }

    public function getConsoleRouterConfig()
    {
        return [
            'routes' => [
                'graphql-skeleton' => [
                    'type' => 'simple',
                    'options' => [
                        'route'    => 'graphql:config-skeleton [--hydrator-sections=] [--object-manager=]',
                        'defaults' => [
                            'controller' => Console\ConfigurationSkeletonController::class,
                            'action' => 'index'
                        ],
                    ],
                ],
            ],
        ];
    }
}
