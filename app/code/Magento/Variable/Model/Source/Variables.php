<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Model\Source;

/**
 * Store Contact Information source model.
 */
class Variables implements \Magento\Framework\Option\ArrayInterface
{
    const DEFAULT_VARIABLE_TYPE = "default";

    /**
     * Assoc array of configuration variables.
     *
     * @var array
     */
    private $configVariables = [];

    /**
     * Constructor.
     *
     * @param \Magento\Config\Model\Config\Structure\SearchInterface $configStructure
     * @param array $configPaths
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(
        \Magento\Config\Model\Config\Structure\SearchInterface $configStructure,
        array $configPaths = []
    ) {
        foreach ($configPaths as $groupPath => $groupElements) {
            $groupPathElements = explode('/', $groupPath);
            $path = [];
            $labels = [];
            foreach ($groupPathElements as $groupPathElement) {
                $path[] = $groupPathElement;
                $labels[] = __(
                    $configStructure->getElementByConfigPath(implode('/', $path))->getLabel()
                );
            }
            $this->configVariables[$groupPath]['label'] = implode(' / ', $labels);
            foreach (array_keys($groupElements) as $elementPath) {
                $this->configVariables[$groupPath]['elements'][] = [
                    'value' => $elementPath,
                    'label' => __($configStructure->getElementByConfigPath($elementPath)->getLabel()),
                ];
            }
        }
        $this->configVariables;
    }

    /**
     * Retrieve option array of store contact variables.
     *
     * @param bool $withGroup
     * @return array
     */
    public function toOptionArray($withGroup = false)
    {
        $optionArray = [];
        if ($withGroup) {
            foreach ($this->configVariables as $configVariableGroup) {
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
            foreach ($this->configVariables as $configVariableGroup) {
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
     * Return available config variables.
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getData()
    {
        return $this->configVariables;
    }
}
