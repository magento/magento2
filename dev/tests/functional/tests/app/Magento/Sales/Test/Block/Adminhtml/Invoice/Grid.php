<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Invoice;

/**
 * Invoice grid on invoice index page.
 */
class Grid extends \Magento\Ui\Test\Block\Adminhtml\DataGrid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => 'input[name="filters[increment_id]"]',
        ],
        'order_id' => [
            'selector' => 'input[name="filters[order_increment_id]"]',
        ],
        'grand_total_from' => [
            'selector' => 'input[name="filters[grand_total][from]"]',
        ],
        'grand_total_to' => [
            'selector' => 'input[name="filters[grand_total][to]"]',
        ],
    ];

    /**
     * Locator value for "View" link inside action column.
     *
     * @var string
     */
    protected $editLink = '.action-menu-item[href*="view"]';
}
