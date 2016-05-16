<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options\Search;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * 'Import custom options to product' grid.
 */
class Grid extends DataGrid
{
    /**
     * Selector for 'Import' button.
     *
     * @var string
     */
    protected $importProducts = '[data-role="action"]';

    /**
     * Filters param for grid.
     *
     * @var array
     */
    protected $filters = [
        'sku' => [
            'selector' => 'input[name=sku]',
        ],
    ];

    /**
     * Press 'Import' button.
     *
     * @return void
     */
    public function addProducts()
    {
        $this->_rootElement->find($this->importProducts)->click();
        $this->getTemplateBlock()->waitForElementNotVisible($this->importProducts);
    }
}
