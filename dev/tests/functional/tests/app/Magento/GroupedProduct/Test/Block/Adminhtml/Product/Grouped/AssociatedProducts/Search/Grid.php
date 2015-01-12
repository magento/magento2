<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Adminhtml\Product\Grouped\AssociatedProducts\Search;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;
use Mtf\Client\Element;

/**
 * Class Grid
 * 'Add Products to Grouped product list' grid
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
    ];

    /**
     * An element locator which allows to select entities in grid
     *
     * @var string
     */
    protected $selectItem = '[data-column=entity_id] input';

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
