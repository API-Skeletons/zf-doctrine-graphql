<?php

namespace ZF\Doctrine\GraphQL\Documentation;

class ApigilityDocumentationProvider implements
    DocumentationProviderInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getEntity($entityName)
    {
        // Documentation for entities is stored in the documentation.php config file.
        // Fetching all those files is outside the scope of work for this class for now.
        return 'Doctrine Entity ' . $entityName;
    }

    /**
     * Populate the field documentation based on teh input filter
     * for the first matching entity found in zf-rest configuration
     */
    public function getField($entityName, $fieldName)
    {
        $inputFilter = null;
        $description = null;

        if (! isset($this->config['zf-rest'])) {
            return null;
        }

        foreach ($this->config['zf-rest'] as $controllerName => $restConfig) {
            if ($restConfig['entity_class'] == $entityName) {
                $inputFilter = $this->config['zf-content-validation'][$controllerName]['input_filter'] ?? null;
                break;
            }
        }

        if ($inputFilter
            && isset($this->config['input_filter_specs'])
            && isset($this->config['input_filter_specs'][$inputFilter])) {
            foreach ($this->config['input_filter_specs'][$inputFilter] as $fieldConfig) {
                if ($fieldConfig['name'] == $fieldName) {
                    $description = $fieldConfig['description'] ?? null;
                    break;
                }
            }
        }

        return $description;
    }
}
