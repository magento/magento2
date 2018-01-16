<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config;

/**
 * Class that stores configuration for processing value of arguments for GraphQl fields
 */
class FieldConfig
{
    /**
     * Map as array for classes that represent field arguments
     *
     * @var array
     */
    private $config = [];

    /**
     * @var array
     */
    private $instances = [];

    /**
     * @var ArgumentConfigFactory
     */
    private $argumentConfigFactory;

    /**
     * @param ArgumentConfigFactory $argumentConfigFactory
     * @param array $config
     */
    public function __construct(ArgumentConfigFactory $argumentConfigFactory, array $config = [])
    {
        $this->config = $config;
        $this->argumentConfigFactory = $argumentConfigFactory;
    }

    /**
     * Returns a field configuration that is configured through DI
     *
     * @param string $fieldName
     * @param array $arguments
     * @return ArgumentConfig[]
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getFieldConfig(string $fieldName, array $arguments)
    {
        if (isset($this->instances[$fieldName])) {
            return $this->instances[$fieldName];
        }
        if (isset($this->config[$fieldName])) {
            $this->processConfiguredField($fieldName, $arguments);
        } else {
            foreach (array_keys($arguments) as $argument) {
                $this->instances[$fieldName][$argument] = $this->argumentConfigFactory->create([
                    'defaultValue' => null,
                    'valueParser' => null
                ]);
            }
        }
        //not all fields have arguments
        if (isset($this->instances[$fieldName])) {
            return $this->instances[$fieldName];
        } else {
            return [];
        }
    }

    private function processConfiguredField(string $fieldName, array $arguments)
    {
        $this->instances[$fieldName] = [];
        foreach ($this->config[$fieldName] as $argumentName => $fieldConfig) {
            $this->instances[$fieldName][$argumentName] = $this->argumentConfigFactory->create(
                [
                    'defaultValue' => isset($fieldConfig['defaultValue']) ? $fieldConfig['defaultValue'] : null,
                    'valueParser'=> isset($fieldConfig['valueParser'])
                        ? $fieldConfig['valueParser']
                        : null
                ]
            );
        }
        foreach (array_keys($arguments) as $argumentName) {
            if (!isset($this->instances[$fieldName][$argumentName])) {
                $this->instances[$fieldName][$argumentName] = $this->argumentConfigFactory->create(
                    [
                        'defaultValue' => null,
                        'valueParser'=> null
                    ]
                );
            }
        }
    }
}
