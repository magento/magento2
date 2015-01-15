<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit;

use Magento\Backend\Test\Block\Widget\Tab;
use Mtf\Client\Element;

/**
 * Class AdvancedPricingTab
 * Product advanced pricing tab
 */
class AdvancedPricingTab extends ProductTab
{
    /**
     * Class name 'Subform' of the main tab form
     *
     * @var array
     */
    protected $childrenForm = [
        'group_price' => 'AdvancedPricingTab\OptionGroup',
        'tier_price' => 'AdvancedPricingTab\OptionTier',
    ];

    /**
     * Fill 'Advanced price' product form on tab
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        $context = $element ? $element : $this->_rootElement;
        foreach ($fields as $fieldName => $field) {
            // Fill form
            if (isset($this->childrenForm[$fieldName]) && is_array($field['value'])) {
                /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\AbstractOptions $optionsForm */
                $optionsForm = $this->blockFactory->create(
                    __NAMESPACE__ . '\\' . $this->childrenForm[$fieldName],
                    ['element' => $context]
                );

                foreach ($field['value'] as $key => $option) {
                    ++$key;
                    $optionsForm->fillOptions(
                        $option,
                        $context->find(
                            '#attribute-' . $fieldName . '-container tbody tr:nth-child(' . $key . ')'
                        )
                    );
                }
            } elseif (!empty($field['value'])) {
                $data = $this->dataMapping([$fieldName => $field]);
                $this->_fill($data, $this->_rootElement);
            }
        }

        return $this;
    }

    /**
     * Get data of tab
     *
     * @param array|null $fields
     * @param Element|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, Element $element = null)
    {
        $formData = [];
        foreach ($fields as $fieldName => $field) {
            // Data collection forms
            if (isset($this->childrenForm[$fieldName]) && is_array($field['value'])) {
                /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\AbstractOptions $optionsForm */
                $optionsForm = $this->blockFactory->create(
                    __NAMESPACE__ . '\\' . $this->childrenForm[$fieldName],
                    ['element' => $this->_rootElement]
                );

                foreach ($field['value'] as $key => $option) {
                    $formData[$fieldName][$key++] = $optionsForm->getDataOptions(
                        $option,
                        $this->_rootElement->find(
                            '#attribute-' . $fieldName . '-container tbody tr:nth-child(' . $key . ')'
                        )
                    );
                }
            } elseif (!empty($field['value'])) {
                $data = $this->dataMapping([$fieldName => $field]);
                $formData += $this->_getData($data, $this->_rootElement);
            }
        }

        return $formData;
    }
}
