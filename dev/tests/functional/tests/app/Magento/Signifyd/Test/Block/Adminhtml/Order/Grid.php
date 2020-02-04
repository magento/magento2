<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Adminhtml\Order;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Admin Data Grid for managing "Sales Order" entities.
 */
class Grid extends DataGrid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => '[name="increment_id"]',
        ],
        'status' => [
            'selector' => '[name="status"]',
            'input' => 'select',
        ],
        'signifyd_guarantee_status' => [
            'selector' => '[name="signifyd_guarantee_status"]',
            'input' => 'select'
        ]
    ];
}
