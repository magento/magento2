<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;

/**
 * Submit Order step.
 */
class SubmitOrderNegativeStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
    }

    /**
     * Fill Sales Data.
     *
     * @return void
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->submitOrder();
    }
}
