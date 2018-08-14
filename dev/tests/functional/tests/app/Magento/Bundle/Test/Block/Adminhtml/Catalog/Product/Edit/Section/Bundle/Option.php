<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle;

use Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle\Option\Search\Grid;
use Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle\Option\Selection;
use Magento\Mtf\Block\Form;

/**
 * Bundle option block on backend.
 */
class Option extends Form
{
    /**
     * Selector block Grid.
     *
     * @var string
     */
    protected $searchGridBlock = ".product_form_product_form_bundle-items_modal";

    /**
     * Added product row.
     *
     * @var string
     */
    protected $selectionBlock = '[data-index="bundle_selections"] > tbody > tr:nth-child(%d)';

    /**
     * Selector for 'Add Products to Option' button.
     *
     * @var string
     */
    protected $addProducts = 'button[data-index="modal_set"]';

    /**
     * Remove selection button selector.
     *
     * @var string
     */
    protected $removeSelection = 'button[data-action="remove_row"]';

    /**
     * Get grid for assigning products for bundle option.
     *
     * @return Grid
     */
    protected function getSearchGridBlock()
    {
        return $this->blockFactory->create(
            \Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle\Option\Search\Grid::class,
            ['element' => $this->browser->find($this->searchGridBlock)]
        );
    }

    /**
     * Get product row assigned to bundle option.
     *
     * @param int $rowIndex
     * @return Selection
     */
    protected function getSelectionBlock($rowIndex)
    {
        return $this->blockFactory->create(
            \Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle\Option\Selection::class,
            ['element' => $this->_rootElement->find(sprintf($this->selectionBlock, ++$rowIndex))]
        );
    }

    /**
     * Fill bundle option.
     *
     * @param array $fields
     * @return void
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
            $this->getSelectionBlock($key)->fillProductRow($field['data']);
        }
    }

    /**
     * Get data bundle option.
     *
     * @param array $fields
     * @return array
     */
    public function getOptionData(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $newField = $this->_getData($mapping);
        foreach ($fields['assigned_products'] as $key => $field) {
            $newField['assigned_products'][$key] = $this->getSelectionBlock($key)->getProductRow($field['data']);
        }
        return $newField;
    }
}
