<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\Grouped\AssociatedProducts\Search;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Associated products grid.
 */
class Grid extends GridInterface
{
    /**
     * 'Add Selected Products' button.
     *
     * @var string
     */
    protected $addProducts = 'button.add';

    /**
     * An element locator which allows to select entities in grid.
     *
     * @var string
     */
    protected $selectItem = '[data-column=entity_id] input';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => '#grouped_grid_popup_filter_name',
        ],
        'sku' => [
            'selector' => '#grouped_grid_popup_filter_sku',
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
