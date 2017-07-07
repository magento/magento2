<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\Search\Grid;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\Section;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\AbstractOptions;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\Row;

/**
 * Product custom options section.
 */
class Options extends Section
{
    /**
     * Custom option row.
     *
     * @var string
     */
    protected $customOptionRow = './/*[*[@class="fieldset-wrapper-title"]//span[.="%s"]]';

    /**
     * New custom option row locator.
     *
     * @var string
     */
    protected $newCustomOptionRow = './/*[@data-index="options"]/tbody/tr[%d]';

    /**
     * Add an option button.
     *
     * @var string
     */
    protected $buttonAddOption = '[data-index="button_add"]';

    /**
     * Import an option button.
     *
     * @var string
     */
    protected $buttonImportOptions = '[data-index="button_import"]';

    /**
     * Import products grid.
     *
     * @var string
     */
    protected $importGrid = ".product_form_product_form_import_options_modal";

    /**
     * Locator for 'Add Value' button.
     *
     * @var string
     */
    protected $addValue = '[data-action="add_new_row"]';

    /**
     * Locator for dynamic data row.
     *
     * @var string
     */
    protected $dynamicDataRow = '[data-index="values"] tbody tr:nth-child(%d)';

    /**
     * Locator for static data row.
     *
     * @var string
     */
    protected $staticDataRow = '[data-index="container_type_static"] div:nth-child(%d)';

    /**
     * Sort rows data.
     *
     * @var array
     */
    protected $sortRowsData = [];

    /**
     * Fill custom options form on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
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
            $this->_rootElement->find($this->buttonAddOption)->click();
            if (!empty($field['options'])) {
                $options = $field['options'];
                unset($field['options']);
            }

            $rootElement = $this->_rootElement->find(
                sprintf($this->newCustomOptionRow, $keyRoot + 1),
                Locator::SELECTOR_XPATH
            );
            $data = $this->dataMapping($field);
            $this->_fill($data, $rootElement);

            // Fill subform
            if (isset($field['type']) && !empty($options)) {
                $this->setOptionTypeData($options, $field['type'], $rootElement);
            }
        }

        return $this;
    }

    /**
     * Import custom options.
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
     * Get grid for import custom options products.
     *
     * @return Grid
     */
    protected function getSearchGridBlock()
    {
        return $this->blockFactory->create(
            \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\Search\Grid::class,
            ['element' => $this->browser->find($this->importGrid)]
        );
    }

    /**
     * Get select option row block.
     *
     * @param int $index
     * @param SimpleElement $element
     * @return Row
     */
    private function getRowBlock($index, SimpleElement $element = null)
    {
        $element = $element ?: $this->_rootElement;
        return $this->blockFactory->create(
            \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\Row::class,
            ['element' => $element->find(sprintf($this->dynamicDataRow, ++$index))]
        );
    }

    /**
     * Set Option Type data.
     *
     * @param array $options
     * @param string $type
     * @param ElementInterface $element
     * @return $this
     */
    private function setOptionTypeData(array $options, $type, ElementInterface $element)
    {
        /** @var AbstractOptions $optionsForm */
        $optionsForm = $this->blockFactory->create(
            __NAMESPACE__ . '\Options\Type\\' . $this->optionNameConvert($type),
            ['element' => $element]
        );
        $context = $element->find($this->addValue)->isVisible()
            ? $this->dynamicDataRow
            : $this->staticDataRow;
        foreach ($options as $key => $option) {
            if (isset($option['sort_order'])) {
                $currentSortOrder = (int)$option['sort_order'];
                unset($option['sort_order']);
            } else {
                $currentSortOrder = 0;
            }
            $optionsForm->fillOptions(
                $option,
                $element->find(sprintf($context, $key + 1))
            );
            $this->sortOption($key, $currentSortOrder, $element);
        }
        $this->sortRowsData = [];

        return $this;
    }

    /**
     * Sort sample element.
     *
     * @param int $position
     * @param int $sortOrder
     * @param SimpleElement|null $element
     * @return void
     */
    private function sortOption($position, $sortOrder, SimpleElement $element = null)
    {
        $currentSortRowData = ['current_position_in_grid' => $position, 'sort_order' => $sortOrder];
        foreach ($this->sortRowsData as &$sortRowData) {
            if ($sortRowData['sort_order'] > $currentSortRowData['sort_order']) {
                // need to reload block because we are changing dom
                $target = $this->getRowBlock($sortRowData['current_position_in_grid'], $element)->getSortHandle();
                $this->getRowBlock($currentSortRowData['current_position_in_grid'], $element)->dragAndDropTo($target);

                $currentSortRowData['current_position_in_grid']--;
                $sortRowData['current_position_in_grid']++;
            }
        }
        unset($sortRowData);
        $this->sortRowsData[] = $currentSortRowData;
    }

    /**
     * Get data of tab.
     *
     * @param array|null $tabFields
     * @param SimpleElement|null $element
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsData($tabFields = null, SimpleElement $element = null)
    {
        $fields = reset($tabFields);
        $name = key($tabFields);
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
                $formDataItem = array_merge(
                    $formDataItem,
                    $this->getOptionTypeData($options, $field['type'], $rootElement)
                );
            }
            $formData[$name][$keyRoot] = $formDataItem;
        }

        return $formData;
    }

    /**
     * Get option type data.
     *
     * @param array $options
     * @param string $type
     * @param ElementInterface $element
     * @return array
     */
    private function getOptionTypeData(array $options, $type, ElementInterface $element)
    {
        $formDataItem = [];
        /** @var AbstractOptions $optionsForm */
        $optionsForm = $this->blockFactory->create(
            __NAMESPACE__ . '\Options\Type\\' . $this->optionNameConvert($type),
            ['element' => $element]
        );
        $context = $element->find($this->addValue)->isVisible()
            ? $this->dynamicDataRow
            : $this->staticDataRow;
        foreach ($options as $key => $option) {
            if (isset($option['sort_order'])) {
                $currentSortOrder = (int)$option['sort_order'];
                unset($option['sort_order']);
            }
            $formDataItem['options'][$key] = $optionsForm->getDataOptions(
                $option,
                $element->find(sprintf($context, $key + 1))
            );
            if (isset($currentSortOrder)) {
                $formDataItem['options'][$key]['sort_order'] = $key;
            }
        }

        return $formDataItem;
    }

    /**
     * Prepare custom options with import options.
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
     * Convert option name.
     *
     * @param string $inputType
     * @return string
     */
    protected function optionNameConvert($inputType)
    {
        $option = substr($inputType, strpos($inputType, "/") + 1);
        $option = str_replace([' ', '&'], "", $option);
        if ($end = strpos($option, '-')) {
            $option = substr($option, 0, $end) . ucfirst(substr($option, ($end + 1)));
        }

        return $option;
    }
}
