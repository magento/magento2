<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Shopcart\Abandoned;

/**
 * Class Grid
 * Abandoned Carts Report grid
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'customer_name' => [
            'selector' => 'input[name="customer_name"]',
        ],
        'email' => [
            'selector' => 'input[name="email"]',
        ],
        'items_count' => [
            'selector' => 'input[name="items_count[from]"]',
        ],
        'items_qty' => [
            'selector' => 'input[name="items_qty[from]"]',
        ],
        'created_at' => [
            'selector' => 'input[name="created_at[from]"]',
            'input' => 'datepicker',
        ],
        'updated_at' => [
            'selector' => 'input[name="updated_at[from]"]',
            'input' => 'datepicker',
        ],
    ];
}
