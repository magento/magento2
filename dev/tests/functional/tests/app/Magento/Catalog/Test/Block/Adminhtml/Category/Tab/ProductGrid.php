<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Tab;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class ProductGrid
 * Products' grid of Category Products tab
 */
class ProductGrid extends Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'sku' => [
            'selector' => '#catalog_category_products_filter_sku',
        ],
        'name' => [
            'selector' => '#catalog_category_products_filter_name',
        ],
    ];

    /**
     * An element locator which allows to select entities in grid
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-in_category';
}
