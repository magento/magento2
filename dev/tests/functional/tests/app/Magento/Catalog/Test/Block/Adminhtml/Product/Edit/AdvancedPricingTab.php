<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit;

use Mtf\ObjectManager;
use Mtf\Client\Element;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Class AdvancedPricingTab
 * Product advanced pricing tab
 */
class AdvancedPricingTab extends Tab
{
    /**
     * Class name 'Subform' of the main tab form
     *
     * @var array
     */
    protected $childrenForm = [
        'group_price' => 'AdvancedPricingTab\OptionGroup',
        'tier_price' => 'AdvancedPricingTab\OptionTier'
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
