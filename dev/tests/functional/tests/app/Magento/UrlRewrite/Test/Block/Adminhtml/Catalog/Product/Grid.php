<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Product;

use Magento\Backend\Test\Block\Widget\Grid as ParentGrid;

/**
 * Class Grid
 * Product grid
 */
class Grid extends ParentGrid
{
    /**
     * An element locator which allows to select entities in grid
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-entity_id';

    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = 'td.col-name';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => '[id=productGrid_product_filter_entity_id]',
        ],
        'sku' => [
            'selector' => '[id=productGrid_product_filter_sku]',
        ],
    ];
}
