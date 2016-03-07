<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider;

/**
 * Class Grouper
 */
class Grouper
{
    /**
     * Default general panel order
     */
    const GENERAL_PANEL_ORDER = 10;

    /**
     * @var array
     */
    protected $meta;

    /**
     * @var array
     */
    protected $groupOptions;

    /**
     * @var string|null
     */
    protected $groupCode;

    /**
     * @var string|null
     */
    protected $elementCode;

    /**
     * @var array
     */
    protected $groupMeta;

    /**
     * @var array
     */
    protected $elementsMeta;

    /**
     * @var array
     */
    protected $defaultGroupOptions = [
        'groupNonSiblings' => false
    ];

    /**
     * @var array
     */
    protected $defaultGroupMeta = [
        'formElement' => 'container',
        'componentType' => 'container',
        'component' => 'Magento_Ui/js/form/components/group',
        'dataScope' => ''
    ];

    /**
     * @var array
     */
    protected $defaultElementOptions = [
        'autoDataScope' => true
    ];

    /**
     * Return updated metadata with the set of elements put into a group
     *
     * @param array $meta
     * @param array $elements
     * @param array $groupOptions
     * @return array
     */
    public function groupMetaElements(array $meta, array $elements, array $groupOptions = [])
    {
        if (!$elements) {
            return $meta;
        }

        $grouped = true;
        $this->meta = $meta;
        $this->groupOptions = array_replace_recursive($this->defaultGroupOptions, $groupOptions);
        $this->groupCode = null;
        $this->elementCode = null;
        $this->groupMeta = $this->defaultGroupMeta;
        $this->elementsMeta = [];

        foreach ($elements as $elementCode => $elementOptions) {
            if (!is_array($elementOptions)) {
                $elementCode = $elementOptions;
                $elementOptions = [];
            }

            $meta = $this->applyElementRequiredOptions($meta, $elementCode, $elementOptions);
            $grouped = $grouped && $this->handleElementOptions($meta, $elementCode, $elementOptions);
        }

        if ($grouped) {
            $this->meta[$this->groupCode]['children'][$this->elementCode] = array_replace_recursive(
                $this->groupMeta,
                ['children' => $this->elementsMeta]
            );

            return $this->meta;
        }

        return $meta;
    }

    /**
     * Handle element options
     *
     * @param array $meta
     * @param string $elementCode
     * @param array $elementOptions
     * @return bool
     */
    protected function handleElementOptions(array $meta, $elementCode, array $elementOptions)
    {
        $groupCode = $this->getGroupCodeByField($meta, $elementCode);

        if (!$groupCode) {
            return false;
        }

        if (!$this->groupOptions['groupNonSiblings'] && $this->groupCode && $this->groupCode != $groupCode) {
            return false;
        }

        $elementOptions = array_replace_recursive($this->defaultElementOptions, $elementOptions);
        $this->handleGroupOptions($meta, $groupCode, $elementCode, $elementOptions);

        $this->elementsMeta[$elementCode] = array_replace_recursive(
            $meta[$groupCode]['children'][$elementCode],
            isset($elementOptions['meta']) ? $elementOptions['meta'] : []
        );

        if ($elementOptions['autoDataScope']) {
            $this->elementsMeta[$elementCode]['dataScope'] = $elementCode;
        }

        unset($this->meta[$groupCode]['children'][$elementCode]);

        return true;
    }

    /**
     * Apply only required portion of element options
     *
     * @param array $meta
     * @param string $elementCode
     * @param array $elementOptions
     * @return array
     */
    protected function applyElementRequiredOptions(array $meta, $elementCode, array $elementOptions)
    {
        if ($groupCode = $this->getGroupCodeByField($meta, $elementCode)) {
            $meta[$groupCode]['children'][$elementCode] = array_replace_recursive(
                $meta[$groupCode]['children'][$elementCode],
                isset($elementOptions['requiredMeta']) ? $elementOptions['requiredMeta'] : []
            );
        }

        return $meta;
    }

    /**
     * Handle group options
     *
     * @param array $meta
     * @param string $groupCode
     * @param string $elementCode
     * @param array $elementOptions
     * @return void
     */
    protected function handleGroupOptions(array $meta, $groupCode, $elementCode, array $elementOptions)
    {
        $isTarget = !empty($elementOptions['isTarget']);

        if (!$this->groupCode || $isTarget) {
            $this->groupCode = $groupCode;
            $this->elementCode = isset($this->groupOptions['targetCode'])
                ? $this->groupOptions['targetCode']
                : $elementCode;
            $this->groupMeta = array_replace_recursive(
                $this->groupMeta,
                [
                    'label' => $this->getElementOption($meta, $groupCode, $elementCode, 'label'),
                    'sortOrder' => $this->getElementOption($meta, $groupCode, $elementCode, 'sortOrder')
                ],
                isset($this->groupOptions['meta']) ? $this->groupOptions['meta'] : []
            );
        }
    }

    /**
     * Retrieve element option from metadata
     *
     * @param array $meta
     * @param string $groupCode
     * @param string $elementCode
     * @param string $optionName
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function getElementOption(array $meta, $groupCode, $elementCode, $optionName, $defaultValue = null)
    {
        return isset($meta[$groupCode]['children'][$elementCode][$optionName])
            ? $meta[$groupCode]['children'][$elementCode][$optionName]
            : $defaultValue;
    }

    /**
     * Get group code by field
     *
     * @param array $meta
     * @param string $field
     * @return string|bool
     */
    protected function getGroupCodeByField(array $meta, $field)
    {
        foreach ($meta as $groupCode => $groupData) {
            if (isset($groupData['children'][$field])) {
                return $groupCode;
            }
        }

        return false;
    }
}
