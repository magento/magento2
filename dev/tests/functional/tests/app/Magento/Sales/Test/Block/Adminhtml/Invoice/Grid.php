<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Invoice;

/**
 * Class Grid
 * Invoice grid on invoice index page
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping
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
}
