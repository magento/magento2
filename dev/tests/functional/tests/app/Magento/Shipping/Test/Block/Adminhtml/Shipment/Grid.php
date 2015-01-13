<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Block\Adminhtml\Shipment;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class Grid
 * Shipment grid on shipment index page
 */
class Grid extends GridInterface
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => 'input[name="real_shipment_id"]',
        ],
        'order_id' => [
            'selector' => 'input[name="order_increment_id"]',
        ],
        'total_qty_from' => [
            'selector' => 'input[name="total_qty[from]"]',
        ],
        'total_qty_to' => [
            'selector' => 'input[name="total_qty[to]"]',
        ],
    ];
}
