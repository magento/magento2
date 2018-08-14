<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Assert invoiced order on admin dashboard.
 */
class AssertInvoicedOrderOnDashboard extends AbstractConstraint
{
    /**
     * Assert orders quantity on admin dashboard.
     *
     * @param TestStepFactory $stepFactory
     * @param array $dashboardOrder
     * @param array $argumentsList
     * @param int $expectedOrdersQuantityOnDashboard
     * @return void
     */
    public function processAssert(
        TestStepFactory $stepFactory,
        array $dashboardOrder,
        array $argumentsList,
        $expectedOrdersQuantityOnDashboard
    ) {
        $orderQty = $stepFactory->create(
            \Magento\Backend\Test\TestStep\GetDashboardOrderStep::class,
            ['argumentsList' => $argumentsList]
        )->run()['dashboardOrder']['quantity'];
        $invoicedOrdersQty = $orderQty - $dashboardOrder['quantity'];

        \PHPUnit_Framework_Assert::assertEquals(
            $invoicedOrdersQty,
            $expectedOrdersQuantityOnDashboard,
            'Order quantity om admin dashboard is not correct.'
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order information on dashboard is correct.';
    }
}
