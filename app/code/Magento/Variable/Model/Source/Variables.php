<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Model\Source;

/**
 * Store Contact Information source model
 */
class Variables implements \Magento\Framework\Option\ArrayInterface
{
    const DEFAULT_VARIABLE_TYPE = "default";

    /**
     * Assoc array of configuration variables
     *
     * @var array
     */
    protected $_configVariables = [];

    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    private $configStructure;

    /**
     * Constructor
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param array $configPaths
     */
    public function __construct(
        \Magento\Config\Model\Config\Structure $configStructure,
        array $configPaths = []
    )
    {
        $this->configStructure = $configStructure;
        foreach ($configPaths as $groupPath => $groupElements) {
            $groupPathElements = explode('/', $groupPath);
            $path = [];
            $labels = [];
            foreach ($groupPathElements as $key => $groupPathElement) {
                $path[] = $groupPathElement;
                $labels[] = __(
                    $configStructure->getElementByConfigPath(implode('/', $path))->getLabel()
                );
            }
            $this->_configVariables[$groupPath]['label'] = implode(' / ', $labels);
            foreach ($groupElements as $elementPath => $groupElement) {
                $this->_configVariables[$groupPath]['elements'][] = [
                    'value' => $elementPath,
                    'label' => __($configStructure->getElementByConfigPath($elementPath)->getLabel()),
                ];
            }
        }
        $this->_configVariables;
    }

    /**
     * Retrieve option array of store contact variables
     *
     * @param bool $withGroup
     * @return array
     */
    public function toOptionArray($withGroup = false)
    {
        $optionArray = [];
        if ($withGroup) {
            foreach ($this->_configVariables as $configVariableGroup) {
                $group = [
                    'label' => $configVariableGroup['label']
                ];
                $groupElements = [];
                foreach ($configVariableGroup['elements'] as $element) {
                    $groupElements[] = [
                        'value' => '{{config path="' . $element['value'] . '"}}',
                        'label' => $element['label'],
                    ];
                }
                $group['value'] = $groupElements;
                $optionArray[] = $group;
            }
        } else {
            foreach ($this->_configVariables as $configVariableGroup) {
                foreach ($configVariableGroup['elements'] as $element) {
                    $optionArray[] = [
                        'value' => '{{config path="' . $element['value'] . '"}}',
                        'label' => $element['label'],
                    ];
                }
            }
        }
        return $optionArray;
    }

    /**
     * Return available config variables
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getData()
    {
        return $this->_configVariables;
    }
}
