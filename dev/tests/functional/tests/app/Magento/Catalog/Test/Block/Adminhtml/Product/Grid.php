<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Backend\Test\Block\Widget\Grid as ParentGrid;

/**
 * Class Grid
 * Backend catalog product grid
 */
class Grid extends ParentGrid
{
    /**
     * Initialize block elements
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => '#productGrid_product_filter_name',
        ],
        'sku' => [
            'selector' => '#productGrid_product_filter_sku',
        ],
        'type' => [
            'selector' => '#productGrid_product_filter_type',
            'input' => 'select',
        ],
        'price_from' => [
            'selector' => '#productGrid_product_filter_price_from',
        ],
        'price_to' => [
            'selector' => '#productGrid_product_filter_price_to',
        ],
        'qty_from' => [
            'selector' => '#productGrid_product_filter_qty_from',
        ],
        'qty_to' => [
            'selector' => '#productGrid_product_filter_qty_to',
        ],
        'visibility' => [
            'selector' => '#productGrid_product_filter_visibility',
            'input' => 'select',
        ],
        'status' => [
            'selector' => '#productGrid_product_filter_status',
            'input' => 'select',
        ],
        'set_name' => [
            'selector' => '#productGrid_product_filter_set_name',
            'input' => 'select',
        ],
    ];

    /**
     * Update attributes for selected items
     *
     * @param array $items
     * @return void
     */
    public function updateAttributes(array $items = [])
    {
        $this->massaction($items, 'Update Attributes');
    }
}
