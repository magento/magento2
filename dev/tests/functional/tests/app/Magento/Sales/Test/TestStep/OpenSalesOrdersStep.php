<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class OpenSalesOrdersStep
 * Open Sales Orders
 */
class OpenSalesOrdersStep implements TestStepInterface
{
    /**
     * Sales order index page
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * @constructor
     * @param OrderIndex $orderIndex
     */
    public function __construct(OrderIndex $orderIndex)
    {
        $this->orderIndex = $orderIndex;
    }

    /**
     * Open Sales order
     *
     * @return void
     */
    public function run()
    {
        $this->orderIndex->open();
    }
}
