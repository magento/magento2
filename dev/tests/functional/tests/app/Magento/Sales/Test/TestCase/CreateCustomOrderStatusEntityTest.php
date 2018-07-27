<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Sales\Test\Fixture\OrderStatus;
use Magento\Sales\Test\Page\Adminhtml\OrderStatusIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderStatusNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Log in as admin.
 * 2. Navigate to the Stores > Settings > Order Status.
 * 3. Click on "Create New Status" button.
 * 4. Fill in all data according to data set.
 * 5. Save order status.
 * 6. Verify created order status.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-23412
 */
class CreateCustomOrderStatusEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Order status page.
     *
     * @var OrderStatusIndex
     */
    protected $orderStatusIndexPage;

    /**
     * Create order status page.
     *
     * @var OrderStatusNew
     */
    protected $orderStatusNewPage;

    /**
     * Inject pages.
     *
     * @param OrderStatusIndex $orderStatusIndexPage
     * @param OrderStatusNew $orderStatusNewPage
     * @return void
     */
    public function __inject(OrderStatusIndex $orderStatusIndexPage, OrderStatusNew $orderStatusNewPage)
    {
        $this->orderStatusIndexPage = $orderStatusIndexPage;
        $this->orderStatusNewPage = $orderStatusNewPage;
    }

    /**
     * Run Create Order Status test.
     *
     * @param OrderStatus $orderStatus
     * @return void
     */
    public function testCreateOrderStatus(OrderStatus $orderStatus)
    {
        // Steps
        $this->orderStatusIndexPage->open();
        $this->orderStatusIndexPage->getGridPageActions()->addNew();
        $this->orderStatusNewPage->getOrderStatusForm()->fill($orderStatus);
        $this->orderStatusNewPage->getFormPageActions()->save();
    }
}
