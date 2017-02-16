<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Signifyd\Test\Block\Adminhtml\Order;

use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Backend Data Grid for managing "Sales Order" entities.
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
