<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block\Adminhtml\Edit\Product;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Review catalog product grid.
 */
class Grid extends DataGrid
{
    /**
     * Grid filter selectors
     *
     * @var array
     */
    protected $filters = [
        'title' => [
            'selector' => 'input[name="title"]',
        ],
        'sku' => [
            'selector' => 'input[name="sku"]',
        ],
    ];
}
