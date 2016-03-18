<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;

use Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid;
use Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Selection;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Class Option
 * Bundle option block on backend
 */
class Option extends Form
{
    /**
     * Selector block Grid
     *
     * @var string
     */
    protected $searchGridBlock = "ancestor::body//aside[contains(@class,'_show') and @data-role='modal']";

    /**
     * Added product row
     *
     * @var string
     */
    protected $selectionBlock = './/tr[contains(@id, "bundle_selection_row_")][not(@style="display: none;")][%d]';

    /**
     * Selector for 'Add Products to Option' button
     *
     * @var string
     */
    protected $addProducts = '[data-ui-id$=add-selection-button]';

    /**
     * Bundle option title
     *
     * @var string
     */
    protected $title = '[name$="[title]"]';

    /**
     * Remove selection button selector
     *
     * @var string
     */
    protected $removeSelection = '.col-actions .action-delete';

    /**
     * Get grid for assigning products for bundle option
     *
     * @return Grid
     */
    protected function getSearchGridBlock()
    {
        return $this->blockFactory->create(
            'Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid',
            ['element' => $this->_rootElement->find($this->searchGridBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get product row assigned to bundle option
     *
     * @param int $rowIndex
     * @return Selection
     */
    protected function getSelectionBlock($rowIndex)
    {
        return $this->blockFactory->create(
            'Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Selection',
            ['element' => $this->_rootElement->find(sprintf($this->selectionBlock, $rowIndex), Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Fill bundle option
     *
     * @param array $fields
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fillOption(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping);
        $selections = $this->_rootElement->getElements($this->removeSelection);
        if (count($selections)) {
            foreach (array_reverse($selections) as $itemSelection) {
                $itemSelection->click();
            }
        }
        foreach ($fields['assigned_products'] as $key => $field) {
            $this->_rootElement->find($this->addProducts)->click();
            $searchBlock = $this->getSearchGridBlock();
            $searchBlock->searchAndSelect($field['search_data']);
            $searchBlock->addProducts();
            $this->getSelectionBlock(++$key)->fillProductRow($field['data']);
        }
    }

    /**
     * Get data bundle option
     *
     * @param array $fields
     * @return array
     */
    public function getOptionData(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $newField = $this->_getData($mapping);
        foreach ($fields['assigned_products'] as $key => $field) {
            $newField['assigned_products'][$key] = $this->getSelectionBlock($key + 1)->getProductRow($field['data']);
        }
        return $newField;
    }
}
