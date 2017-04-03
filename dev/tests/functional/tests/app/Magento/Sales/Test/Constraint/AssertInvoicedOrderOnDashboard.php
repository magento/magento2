<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Assert invoiced order on admin dashboard.
 */
class AssertInvoicedOrderOnDashboard extends AbstractConstraint
{
    /**
     * Invoiced orders quantity.
     */
    const EXPECTED_ORDERS_QTY = 1;

    /**
     * Assert orders quantity and graph image visibility on admin dashboard.
     *
     * @param TestStepFactory $stepFactory
     * @param Dashboard $dashboard
     * @param array $dashboardOrder
     * @param array $argumentsList
     * @return void
     * @internal param Browser $browser
     */
    public function processAssert(
        TestStepFactory $stepFactory,
        Dashboard $dashboard,
        array $dashboardOrder,
        array $argumentsList
    ) {
        $orderQty = $stepFactory->create(
            \Magento\Checkout\Test\TestStep\GetDashboardOrderStep::class,
            ['argumentsList' => $argumentsList]
        )->run()['dashboardOrder']['quantity'];
        $invoicedOrdersQty = $orderQty - $dashboardOrder['quantity'];

        \PHPUnit_Framework_Assert::assertEquals(
            $invoicedOrdersQty,
            self::EXPECTED_ORDERS_QTY,
            'Order quantity om admin dashboard is not correct.'
        );

        \PHPUnit_Framework_Assert::assertTrue(
            $dashboard->getMainBlock()->isGraphImageVisible(),
            'Graph image is not visible on admin dashboard.'
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
