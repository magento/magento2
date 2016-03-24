<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Bundle\Option\Search;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * 'Add Products to Bundle Option' grid.
 */
class Grid extends DataGrid
{
    /**
     * Selector for 'Add Selected Products' button.
     *
     * @var string
     */
    protected $addProducts = '.action-primary[data-role="action"]';

    /**
     * Filters param for grid.
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => 'input[name=name]',
        ],
        'sku' => [
            'selector' => 'input[name=sku]',
        ],
    ];

    /**
     * Press 'Add Selected Products' button.
     *
     * @return void
     */
    public function addProducts()
    {
        $this->_rootElement->find($this->addProducts)->click();
    }
}
