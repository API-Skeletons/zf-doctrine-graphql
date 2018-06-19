<?php

namespace ZF\Doctrine\GraphQL\Console;

use Exception;
use Interop\Container\ContainerInterface;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\Config\Config;
use Zend\Config\Writer\PhpArray;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use ZF\Doctrine\GraphQL\Hydrator\Strategy;
use ZF\Doctrine\GraphQL\Hydrator\Filter;

final class ConfigurationSkeletonController extends AbstractConsoleController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function indexAction()
    {
        $objectManagerAlias = $this->params()->fromRoute('object-manager') ?? 'doctrine.entitymanager.orm_default';
        $hydratorNamespace = $this->params()->fromRoute('hydrator-namespace') ?? '\\Default';

        if (! $this->container->has($objectManagerAlias)) {
            throw new Exception('Invalid object manager alias');
        }
        $objectManager = $this->container->get($objectManagerAlias);

        $metadata = $objectManager->getMetadataFactory()->getAllMetadata();

        $config = [
            'zf-doctrine-graphql' => [
                'limit' => 2000,
                'use_hydrator_cache' => true,
            ],
            'zf-doctrine-graphql-hydrator' => []
        ];

        foreach ($metadata as $classMetadata) {
            $hydratorAlias = 'ZF\\Doctrine\\GraphQL\\Hydrator\\'
                . str_replace('\\', '_', $classMetadata->getName())
                . $hydratorNamespace
                ;

            $strategies = [];
            $filters = [];
            foreach ($classMetadata->getAssociationNames() as $associationName) {
                $mapping = $classMetadata->getAssociationMapping($associationName);

                // See comment on NullifyOwningAssociation for details of why this is done
                if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                    $strategies[$associationName] = Strategy\NullifyOwningAssociation::class;
                } else {
                    $strategies[$associationName] = Strategy\AssociationDefault::class;
                }
            }

            foreach ($classMetadata->getFieldNames() as $fieldName) {
                $fieldMetadata = $classMetadata->getFieldMapping($fieldName);

                // Handle special named fields
                if ($fieldName == 'password' || $fieldName == 'secret') {
                    $filters['password'] = [
                        'condition' => 'and',
                        'filter' => Filter\Password::class,
                    ];
                    continue;
                }

                // Handle all other fields
                switch ($fieldMetadata['type']) {
                    case 'tinyint':
                    case 'smallint':
                    case 'integer':
                    case 'int':
                    case 'bigint':
                        $strategies[$fieldName] = Strategy\ToInteger::class;
                        break;
                    case 'boolean':
                        $strategies[$fieldName] = Strategy\ToBoolean::class;
                        break;
                    case 'decimal':
                    case 'float':
                        $strategies[$fieldName] = Strategy\ToFloat::class;
                        break;
                    case 'string':
                    case 'text':
                    case 'datetime':
                    default:
                        $strategies[$fieldName] = Strategy\FieldDefault::class;
                        break;
                }
            }

            $filters['default'] = [
                'condition' => 'and',
                'filter' => Filter\FilterDefault::class,
            ];

            $config['zf-doctrine-graphql-hydrator'][$hydratorAlias] = [
                'entity_class' => $classMetadata->getName(),
                'object_manager' => $objectManagerAlias,
                'by_value' => true,
                'use_generated_hydrator' => true,
                'naming_strategy' => null,
                'hydrator' => null,
                'strategies' => $strategies,
                'filters' => $filters,
            ];
        }

        $configObject = new Config($config);
        $writer = new PhpArray();
        $writer->setUseBracketArraySyntax(true);
        $writer->setUseClassNameScalars(true);

        echo $writer->toString($configObject);
    }
}
