<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Search;

/**
 * Class Grid
 * Adminhtml sales order create search products block
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * An element locator which allows to select entities in grid
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-in_products input';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'sku' => [
            'selector' => '#sales_order_create_search_grid_filter_sku',
        ],
    ];
}
