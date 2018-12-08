<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\OrderHistory;

/**
 * Assert that "Reorder" button is absent in Orders grid on frontend.
 */
class AssertReorderButtonIsNotVisibleOnFrontend extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that "Reorder" button is absent in Orders grid on frontend.
     *
     * @param OrderInjectable $order
     * @param Customer $customer
     * @param ObjectManager $objectManager
     * @param CustomerAccountIndex $customerAccountIndex
     * @param OrderHistory $orderHistory
     * @param string $status
     * @param string $orderId
     * @param string|null $statusToCheck
     * @return void
     */
    public function processAssert(
        OrderInjectable $order,
        Customer $customer,
        ObjectManager $objectManager,
        CustomerAccountIndex $customerAccountIndex,
        OrderHistory $orderHistory,
        $status = null,
        $orderId = '',
        $statusToCheck = null
    ) {
        $filter = [
            'id' => $order->hasData('id') ? $order->getId() : $orderId,
            'status' => $statusToCheck === null ? $status : $statusToCheck,
        ];

        $objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Orders');
        $errorMessage = implode(', ', $filter);
        \PHPUnit\Framework\Assert::assertFalse(
            $orderHistory->getOrderHistoryBlock()->isReorderButtonPresentByOrderId($filter['id']),
            '"Reorder" button for order with following data \'' . $errorMessage
            . '\' is present in Orders block on frontend.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return '"Reorder" button is not present in orders on frontend.';
    }
}
