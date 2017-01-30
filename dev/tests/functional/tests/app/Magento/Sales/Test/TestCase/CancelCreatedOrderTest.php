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
 * 3. Create order
 *
 * Steps:
 * 1. Login to backend.
 * 2. Sales > Orders.
 * 3. Open the created order.
 * 4. Do cancel Order.
 * 5. Perform all assetions.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-28191
 */
class CancelCreatedOrderTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
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
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'checkmo, flatrate', 'rollback' => true]
        )->run();
    }

    /**
     * Inject pages
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
     * Cancel created order.
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
        $this->salesOrderView->getPageActions()->cancel();

        return [
            'customer' => $order->getDataFieldConfig('customer_id')['source']->getCustomer(),
        ];
    }
}
