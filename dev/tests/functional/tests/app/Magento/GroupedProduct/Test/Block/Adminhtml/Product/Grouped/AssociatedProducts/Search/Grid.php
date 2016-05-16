<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\Search;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * 'Add Products to Grouped product list' grid.
 */
class Grid extends DataGrid
{
    /**
     * 'Add Selected Products' button
     *
     * @var string
     */
    protected $addProducts = '.action-primary[data-role="action"]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => '[name="name"]',
        ],
    ];

    /**
     * Press 'Add Selected Products' button
     *
     * @return void
     */
    public function addProducts()
    {
        $this->_rootElement->find($this->addProducts)->click();
    }
}
