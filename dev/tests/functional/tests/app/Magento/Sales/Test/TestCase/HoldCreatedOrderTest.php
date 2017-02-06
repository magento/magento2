<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Enable payment method "Check/Money Order".
 * 2. Enable shipping method one of "Flat Rate".
 * 3. Create order.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Go to Sales > Orders.
 * 3. Open the created order.
 * 4. Do 'Hold' for Order.
 * 5. Perform all assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-28214
 */
class HoldCreatedOrderTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Orders Page.
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * Enable "Check/Money Order" and "Flat Rate" in configuration.
     *
     * @return void
     */
    public function __prepare()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'checkmo, flatrate', 'rollback' => true]
        )->run();
    }

    /**
     * Inject pages.
     *
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @return void
     */
    public function __inject(OrderIndex $orderIndex, SalesOrderView $salesOrderView)
    {
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
    }

    /**
     * Put created order on hold.
     *
     * @param OrderInjectable $order
     * @return array
     */
    public function test(OrderInjectable $order)
    {
        // Preconditions
        $order->persist();

        // Steps
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $this->salesOrderView->getPageActions()->hold();

        return [
            'customer' => $order->getDataFieldConfig('customer_id')['source']->getCustomer(),
        ];
    }
}
