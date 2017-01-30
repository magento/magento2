<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Sales\Test\Fixture\OrderStatus;
use Magento\Sales\Test\Page\Adminhtml\OrderStatusIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Custom Order Status is created.
 * 2. Order Status assigned to State.
 *
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate to the Stores > Settings > Order Status.
 * 3. Click "Unassign" for appropriate status.
 * 4. Perform all assertions.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-29450
 */
class UnassignCustomOrderStatusTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Order Status Index page.
     *
     * @var OrderStatusIndex
     */
    protected $orderStatusIndex;

    /**
     * Injection data.
     *
     * @param OrderStatusIndex $orderStatusIndex
     * @return void
     */
    public function __inject(OrderStatusIndex $orderStatusIndex)
    {
        $this->orderStatusIndex = $orderStatusIndex;
    }

    /**
     * Run Unassign Custom OrderStatus test.
     *
     * @param OrderStatus $orderStatus
     * @return void
     */
    public function test(OrderStatus $orderStatus)
    {
        // Preconditions:
        $orderStatus->persist();

        // Steps:
        $orderStatusLabel = $orderStatus->getLabel();
        $this->orderStatusIndex->open();
        $this->orderStatusIndex->getOrderStatusGrid()->searchAndUnassign(['label' => $orderStatusLabel]);
    }
}
