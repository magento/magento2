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
 * Class CustomOptionsTab
 * Product custom options tab
 */
class CustomOptionsTab extends Tab
{
    /**
     * Custom option row CSS locator
     *
     * @var string
     */
    protected $customOptionRow = '#product-custom-options-content .fieldset-wrapper:nth-child(%d)';

    /**
     * Class name 'Subform' of the main tab form
     *
     * @var array
     */
    protected $childrenForm = [
        'Field' => 'CustomOptionsTab\OptionField',
        'Drop-down' => 'CustomOptionsTab\OptionDropDown'
    ];

    /**
     * Add an option button
     *
     * @var string
     */
    protected $buttonFormLocator = '[data-ui-id="admin-product-options-add-button"]';

    /**
     * Fill custom options form on tab
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        $fields = reset($fields);
        if (empty($fields['value']) || !is_array($fields['value'])) {
            return $this;
        }

        foreach ($fields['value'] as $keyRoot => $field) {
            $options = null;
            $this->_rootElement->find($this->buttonFormLocator)->click();
            if (!empty($field['options'])) {
                $options = $field['options'];
                unset($field['options']);
            }

            $rootElement = $this->_rootElement->find(sprintf($this->customOptionRow, $keyRoot + 1));
            $data = $this->dataMapping($field);
            $this->_fill($data, $rootElement);

            // Fill subform
            if (isset($field['type']) && isset($this->childrenForm[$field['type']])
                && !empty($options)
            ) {
                /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Options $optionsForm */
                $optionsForm = $this->blockFactory->create(
                    __NAMESPACE__ . '\\' . $this->childrenForm[$field['type']],
                    ['element' => $rootElement]
                );

                foreach ($options as $key => $option) {
                    ++$key;
                    $optionsForm->fillOptions(
                        $option,
                        $rootElement->find('.fieldset .data-table tbody tr:nth-child(' . $key . ')')
                    );
                }
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
        $fields = reset($fields);
        $formData = [];
        if (empty($fields['value'])) {
            return $formData;
        }

        foreach ($fields['value'] as $keyRoot => $field) {
            $formDataItem = null;
            $options = null;
            if (!empty($field['options'])) {
                $options = $field['options'];
                unset($field['options']);
            }

            $rootLocator = sprintf($this->customOptionRow, $keyRoot + 1);
            $rootElement = $this->_rootElement->find($rootLocator);
            $this->waitForElementVisible($rootLocator);
            $data = $this->dataMapping($field);
            $formDataItem = $this->_getData($data, $rootElement);

            // Data collection subform
            if (isset($field['type']) && isset($this->childrenForm[$field['type']])
                && !empty($options)
            ) {
                /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Options $optionsForm */
                $optionsForm = $this->blockFactory->create(
                    __NAMESPACE__ . '\\' . $this->childrenForm[$field['type']],
                    ['element' => $rootElement]
                );

                foreach ($options as $key => $option) {
                    $formDataItem['options'][$key++] = $optionsForm->getDataOptions(
                        $option,
                        $rootElement->find('.fieldset .data-table tbody tr:nth-child(' . $key . ')')
                    );
                }
            }
            $formData[$fields['attribute_code']][$keyRoot] = $formDataItem;
        }

        return $formData;
    }
}
