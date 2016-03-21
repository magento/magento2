<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Edit\Section;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Products grid of Category Products section.
 */
class ProductGrid extends Grid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'in_category' => [
            'selector' => '#catalog_category_products_filter_in_category',
            'input' => 'select'
        ],
        'sku' => [
            'selector' => '#catalog_category_products_filter_sku',
        ],
        'name' => [
            'selector' => '#catalog_category_products_filter_name',
        ],
    ];

    /**
     * An element locator which allows to select entities in grid.
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-in_category';
}
