<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\CreditMemo;

/**
 * Credit memo grid on Credit memos index page.
 */
class Grid extends \Magento\Ui\Test\Block\Adminhtml\DataGrid
{
    /**
     * Locator value for "View" link inside action column.
     *
     * @var string
     */
    protected $editLink = '.data-grid-actions-cell a';

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
            'selector' => 'input[name="base_grand_total[from]"]',
        ],
        'grand_total_to' => [
            'selector' => 'input[name="base_grand_total[to]"]',
        ],
    ];
}
