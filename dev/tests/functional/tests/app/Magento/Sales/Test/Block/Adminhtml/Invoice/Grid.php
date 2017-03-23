<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            'selector' => 'input[name="increment_id"]',
        ],
        'order_id' => [
            'selector' => 'input[name="order_increment_id"]',
        ],
        'grand_total_from' => [
            'selector' => 'input[name="grand_total[from]"]',
        ],
        'grand_total_to' => [
            'selector' => 'input[name="grand_total[to]"]',
        ],
    ];

    /**
     * Locator value for "View" link inside action column.
     *
     * @var string
     */
    protected $editLink = '.action-menu-item[href*="view"]';
}
