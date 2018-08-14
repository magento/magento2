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
 * Assert order graph image is visible on admin dashboard.
 */
class AssertOrderGraphImageIsVisible extends AbstractConstraint
{
    /**
     * Assert order graph image is visible on admin dashboard.
     *
     * @param TestStepFactory $stepFactory
     * @param Dashboard $dashboard
     * @param array $argumentsList
     * @return void
     */
    public function processAssert(
        TestStepFactory $stepFactory,
        Dashboard $dashboard,
        array $argumentsList
    ) {
        $stepFactory->create(
            \Magento\Backend\Test\TestStep\GetDashboardOrderStep::class,
            ['argumentsList' => $argumentsList]
        )->run();

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
        return 'Order graph image is visible on the dashboard.';
    }
}
