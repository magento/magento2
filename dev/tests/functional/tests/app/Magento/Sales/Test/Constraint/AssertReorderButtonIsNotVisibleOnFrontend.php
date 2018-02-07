<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;

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
     * @param TestStepFactory $testStepFactory
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
        TestStepFactory $testStepFactory,
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

        /** @var \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep $loginStep */
        $loginStep = $testStepFactory->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        );
        $loginStep->run();

        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Orders');
        $errorMessage = implode(', ', $filter);

        \PHPUnit_Framework_Assert::assertFalse(
            $orderHistory->getOrderHistoryBlock()->isReorderButtonPresentByOrderId($filter['id']),
            '"Reorder" button for order with following data \'' . $errorMessage
            . '\' is present in Orders block on frontend.'
        );

        $loginStep->cleanup();
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
