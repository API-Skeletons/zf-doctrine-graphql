<?php

namespace ZF\Doctrine\GraphQL;

use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    /**
     * @codeCoverageIgnore
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'hydrators' => $this->getHydratorConfig(),
            'controllers' => $this->getControllerConfig(),
            'console' => [
                'router' => $this->getConsoleRouterConfig(),
            ],
            'zf-doctrine-graphql-type' => $this->getDoctrineGraphQLTypeConfig(),
            'zf-doctrine-graphql-filter' => $this->getDoctrineGraphQLFilterConfig(),
            'zf-doctrine-graphql-criteria' => $this->getDoctrineGraphQLCriteriaConfig(),
            'zf-doctrine-graphql-resolve' => $this->getDoctrineGraphQLResolveConfig(),
        ];
    }

    public function getDependencyConfig()
    {
        return [
            'aliases' => [
                'ZF\Doctrine\GraphQL\Hydrator\HydratorExtractTool' => Hydrator\HydratorExtractToolDefault::class,
                'ZF\Doctrine\GraphQL\Documentation\DocumentationProvider'
                    => Documentation\HydratorConfigurationDocumentationProvider::class,
            ],
            'factories' => [
                Hydrator\HydratorExtractToolDefault::class => Hydrator\HydratorExtractToolDefaultFactory::class,

                Hydrator\Filter\FilterDefault::class => InvokableFactory::class,
                Hydrator\Filter\Password::class => InvokableFactory::class,
                Hydrator\Strategy\ToBoolean::class => InvokableFactory::class,
                Hydrator\Strategy\ToFloat::class => InvokableFactory::class,
                Hydrator\Strategy\ToInteger::class => InvokableFactory::class,
                Hydrator\Strategy\ToJson::class => InvokableFactory::class,
                Hydrator\Strategy\NullifyOwningAssociation::class => InvokableFactory::class,
                Hydrator\Strategy\AssociationDefault::class => InvokableFactory::class,
                Hydrator\Strategy\FieldDefault::class => InvokableFactory::class,

                Criteria\CriteriaManager::class => Criteria\CriteriaManagerFactory::class,
                Field\FieldResolver::class => Field\FieldResolverFactory::class,
                Filter\Loader::class => Filter\LoaderFactory::class,
                Filter\FilterManager::class => Filter\FilterManagerFactory::class,
                Resolve\ResolveManager::class => Resolve\ResolveManagerFactory::class,
                Resolve\Loader::class => Resolve\LoaderFactory::class,
                Type\Loader::class => Type\LoaderFactory::class,
                Type\TypeManager::class => Type\TypeManagerFactory::class,

                Documentation\HydratorConfigurationDocumentationProvider::class =>
                    Documentation\HydratorConfigurationDocumentationProviderFactory::class,
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
                'datetime' => Type\DateTimeType::class,
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
                Criteria\CriteriaTypeAbstractFactory::class,
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
