<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Client\Browser;

/**
 * Assert invoiced order on admin dashboard.
 */
class AssertInvoicedOrderOnDashboard extends AbstractConstraint
{
    /**
     * Graph image selector.
     *
     * @var string
     */
    private $graphImage = '#diagram_tab_orders_content .dashboard-diagram-chart';

    /**
     * Assert orders quantity and graph image visibility on admin dashboard.
     *
     * @param TestStepFactory $stepFactory
     * @param Browser $browser
     * @param array $dashboardOrder
     * @param array $items
     * @return void
     */
    public function processAssert(
        TestStepFactory $stepFactory,
        Browser $browser,
        array $dashboardOrder,
        array $items
    ) {
        $orderQty = $stepFactory->create(
            \Magento\Checkout\Test\TestStep\GetDashboardOrderStep::class,
            ['items' => $items]
        )->run()['dashboardOrder']['quantity'];

        \PHPUnit_Framework_Assert::assertEquals(
            $dashboardOrder['quantity'] + 1,
            $orderQty,
            'Order quantity om admin dashboard is not correct.'
        );

        \PHPUnit_Framework_Assert::assertTrue(
            $browser->find($this->graphImage)->isVisible(),
            'Graph image is not visible on admin dashboard.'
        );
    }

    /**
     * Returns string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Order information on dashboard is correct.';
    }
}
