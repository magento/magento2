<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\Grouped\AssociatedProducts\Search;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class Grid
 *
 */
class Grid extends GridInterface
{
    /**
     * 'Add Selected Products' button
     *
     * @var string
     */
    protected $addProducts = 'button.add';

    /**
     * Filters array mapping
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
     * Initialize block elements
     */
    protected function _init()
    {
        parent::_init();
        $this->selectItem = "[data-column=entity_id] input";
    }

    /**
     * Press 'Add Selected Products' button
     */
    public function addProducts()
    {
        $this->_rootElement->find($this->addProducts)->click();
    }
}
