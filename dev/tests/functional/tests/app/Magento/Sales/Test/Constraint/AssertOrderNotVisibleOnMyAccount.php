<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\OrderHistory;
use Mtf\Constraint\AbstractConstraint;
use Mtf\ObjectManager;

/**
 * Class AssertOrderNotVisibleOnMyAccount
 * Assert order is not visible in customer account on frontend
 */
class AssertOrderNotVisibleOnMyAccount extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert order is not visible in customer account on frontend
     *
     * @param OrderInjectable $order
     * @param CustomerInjectable $customer
     * @param ObjectManager $objectManager
     * @param CustomerAccountIndex $customerAccountIndex
     * @param OrderHistory $orderHistory
     * @param string $status
     * @return void
     */
    public function processAssert(
        OrderInjectable $order,
        CustomerInjectable $customer,
        ObjectManager $objectManager,
        CustomerAccountIndex $customerAccountIndex,
        OrderHistory $orderHistory,
        $status
    ) {
        $filter = [
            'id' => $order->getId(),
            'status' => $status,
        ];
        $customerLogin = $objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        );
        $customerLogin->run();
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Orders');
        \PHPUnit_Framework_Assert::assertFalse(
            $orderHistory->getOrderHistoryBlock()->isOrderVisible($filter),
            'Order with following data \'' . implode(', ', $filter) . '\' is present in Orders block on frontend.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Sales order absent in orders on frontend.';
    }
}
