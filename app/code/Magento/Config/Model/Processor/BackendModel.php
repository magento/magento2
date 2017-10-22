<?php
/**
 * Backend model configuration values processor. Process configuration values with backend models if needed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Processor;

use Magento\Config\Model\Config\Structure\Reader as ConfigReader;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;

/**
 * Backend model configuration values processor. Process configuration values with backend models if needed.
 */
class BackendModel implements PostProcessorInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * Avoid circular dependencies while creating backend models,
     * some of them rely on configuration, running this processor again and trying to create again same
     * backend model
     *
     * @var bool
     */
    private $processed = false;

    /**
     * BackendModel constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigReader $configReader
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ConfigReader $configReader
    ) {
        $this->objectManager = $objectManager;
        $this->configReader = $configReader;
    }

    /**
     * @inheritdoc
     */
    public function process(array $data)
    {
        if ($this->processed) {
            return $data;
        }

        $this->processed = true;
        $backendModelInfo = $this->parseConfigData($this->configReader->read(Area::AREA_ADMINHTML));

        foreach ($backendModelInfo as $path => $backendModel) {
            $pathParts = explode('/', $path);

            if (count($pathParts) != 3) {
                continue;
            }

            /** @var \Magento\Framework\App\Config\Value $backendModel */
            $backendModel = $this->objectManager->get($backendModel);
            list($section, $group, $field) = $pathParts;

            foreach ($data as $scope => $scopeData) {
                switch ($scope) {
                    case 'default':
                        $data[$scope] = $this->processFieldValue($scopeData, $backendModel, $section, $group, $field);
                        break;
                    default:
                        foreach ($scopeData as $sKey => $sData) {
                            $data[$scope][$sKey] =
                                $this->processFieldValue($sData, $backendModel, $section, $group, $field);
                        }
                }
            }
        }
        return $data;
    }

    /**
     * Parse full system.xml config data
     *
     * @param array $configData
     * @return array
     */
    private function parseConfigData($configData)
    {
        $parsedData = [];
        if (isset($configData['config']['system']['sections'])) {
            foreach ($configData['config']['system']['sections'] as $section) {
                $parsedData = $this->parseSectionData($section, $parsedData);
            }
        }
        return $parsedData;
    }

    /**
     * Parse section data
     *
     * @param array $section
     * @param array $parsedData
     * @return array
     */
    private function parseSectionData($section, $parsedData)
    {
        if (isset($section['children'])) {
            foreach ($section['children'] as $group) {
                $parsedData = $this->parseGroupData($group, $parsedData);
            }
        }
        return $parsedData;
    }

    /**
     * Parse group data
     *
     * @param array $group
     * @param array $parsedData
     * @return array
     */
    private function parseGroupData($group, $parsedData)
    {
        if (isset($group['children'])) {
            foreach ($group['children'] as $field) {
                $parsedData = $this->parseFieldData($field, $parsedData);
            }
        }
        return $parsedData;
    }

    /**
     * Parse field data
     *
     * @param array $field
     * @param array $parsedData
     * @return array
     */
    private function parseFieldData($field, $parsedData)
    {
        $path = implode('/', [$field['path'], $field['id']]);
        if (!isset($parsedData[$path]) && isset($field['backend_model'])) {
            $parsedData[$path] = $field['backend_model'];
        }
        return $parsedData;
    }

    /**
     * @param array $scopeData
     * @param \Magento\Framework\App\Config\Value $backendModel
     * @param string $section
     * @param string $group
     * @param string $field
     * @return array
     */
    private function processFieldValue($scopeData, $backendModel, $section, $group, $field)
    {
        if (isset($scopeData[$section][$group][$field])) {
            $backendModel->setValue($scopeData[$section][$group][$field]);
            $backendModel->afterLoad();
            $scopeData[$section][$group][$field] = $backendModel->getValue();
        }

        return $scopeData;
    }
}
