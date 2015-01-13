<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\Search\Grid;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Options
 * Product custom options tab
 */
class Options extends Tab
{
    /**
     * Custom option row
     *
     * @var string
     */
    protected $customOptionRow = '//*[*[@class="fieldset-wrapper-title"]//span[.="%s"]]';

    /**
     * New custom option row CSS locator
     *
     * @var string
     */
    protected $newCustomOptionRow = '#product-custom-options-content .fieldset-wrapper:nth-child(%d)';

    /**
     * Add an option button
     *
     * @var string
     */
    protected $buttonFormLocator = '[data-ui-id="admin-product-options-add-button"]';

    /**
     * Import an option button
     *
     * @var string
     */
    protected $buttonImportOptions = '[data-ui-id="admin-product-options-import-button"]';

    /**
     * Selector block import products grid
     *
     * @var string
     */
    protected $importGrid = "//ancestor::body/div[*[@id='import-container'] and contains(@style,'display: block')]";

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
            if ($keyRoot === 'import') {
                $this->importOptions($field['products']);
                continue;
            }
            $options = null;
            $this->_rootElement->find($this->buttonFormLocator)->click();
            if (!empty($field['options'])) {
                $options = $field['options'];
                unset($field['options']);
            }

            $rootElement = $this->_rootElement->find(sprintf($this->newCustomOptionRow, $keyRoot + 1));
            $data = $this->dataMapping($field);
            $this->_fill($data, $rootElement);

            // Fill subform
            if (isset($field['type']) && !empty($options)) {
                /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\AbstractOptions $optionsForm */
                $optionsForm = $this->blockFactory->create(
                    __NAMESPACE__ . '\Options\Type\\' . $this->optionNameConvert($field['type']),
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
     * Import custom options
     *
     * @param array $products
     * @return void
     */
    protected function importOptions(array $products)
    {
        foreach ($products as $product) {
            $this->_rootElement->find($this->buttonImportOptions)->click();
            $searchBlock = $this->getSearchGridBlock();
            $searchBlock->searchAndSelect(['sku' => $product]);
            $searchBlock->addProducts();
        }
    }

    /**
     * Get grid for import custom options products
     *
     * @return Grid
     */
    protected function getSearchGridBlock()
    {
        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\Search\Grid',
            ['element' => $this->_rootElement->find($this->importGrid, Locator::SELECTOR_XPATH)]
        );
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
        if (isset($fields['value']['import'])) {
            $fields['value'] = $this->prepareCustomOptions($fields['value']);
        }

        foreach ($fields['value'] as $keyRoot => $field) {
            $formDataItem = null;
            $options = null;
            if (!empty($field['options'])) {
                $options = $field['options'];
                unset($field['options']);
            }

            $rootLocator = sprintf($this->customOptionRow, $field['title']);
            $rootElement = $this->_rootElement->find($rootLocator, Locator::SELECTOR_XPATH);
            $this->waitForElementVisible($rootLocator, Locator::SELECTOR_XPATH);
            $data = $this->dataMapping($field);
            $formDataItem = $this->_getData($data, $rootElement);

            // Data collection subform
            if (isset($field['type']) && !empty($options)) {
                /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\AbstractOptions $optionsForm */
                $optionsForm = $this->blockFactory->create(
                    __NAMESPACE__ . '\Options\Type\\' . $this->optionNameConvert($field['type']),
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

    /**
     * Prepare custom options with import options
     *
     * @param array $options
     * @return array
     */
    protected function prepareCustomOptions(array $options)
    {
        $importOptions = $options['import']['options'];
        $options = array_merge($options, $importOptions);
        unset($options['import']);
        return $options;
    }

    /**
     * Convert option name
     *
     * @param string $str
     * @return string
     */
    protected function optionNameConvert($str)
    {
        $str = str_replace([' ', '&'], "", $str);
        if ($end = strpos($str, '-')) {
            $str = substr($str, 0, $end) . ucfirst(substr($str, ($end + 1)));
        }
        return $str;
    }
}
