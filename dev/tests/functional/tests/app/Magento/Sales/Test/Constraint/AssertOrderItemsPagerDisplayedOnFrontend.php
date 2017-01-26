<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;

/**
 * Assert that order items pager is present on order view on frontend.
 */
class AssertOrderItemsPagerDisplayedOnFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that order items pager is present on order view on frontend.
     *
     * @param OrderInjectable $order
     * @param ObjectManager $objectManager
     * @param CustomerAccountIndex $customerAccountIndex
     * @param OrderHistory $orderHistory
     * @param CustomerOrderView $customerOrderView
     * @param string $orderId
     * @internal param OrderView $orderView
     */
    public function processAssert(
        OrderInjectable $order,
        ObjectManager $objectManager,
        CustomerAccountIndex $customerAccountIndex,
        OrderHistory $orderHistory,
        CustomerOrderView $customerOrderView,
        $orderId = ''
    ) {
        $orderId = $order->hasData('id') ? $order->getId() : $orderId;

        $objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $order->getDataFieldConfig('customer_id')['source']->getCustomer()]
        )->run();
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Orders');
        $orderHistory->getOrderHistoryBlock()->openOrderById($orderId);
        \PHPUnit_Framework_Assert::assertTrue(
            $customerOrderView->getOrderViewBlock()->isTopPagerDisplayed(),
            'Order items top pager is expected to be displayed for order '. $orderId
        );
        \PHPUnit_Framework_Assert::assertTrue(
            $customerOrderView->getOrderViewBlock()->isBottomPagerDisplayed(),
            'Order items bottom pager is expected to be displayed for order '. $orderId
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order items pager is present on frontend.';
    }
}
