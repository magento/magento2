<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block\Adminhtml\Product;

use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;

/**
 * Review catalog product grid.
 */
class Grid extends AbstractGrid
{
    /**
     * First row selector
     *
     * @var string
     */
    protected $firstRowSelector = './/tbody/tr[1]';

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
