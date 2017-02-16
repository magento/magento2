<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Signifyd\Test\Constraint;

use Magento\Signifyd\Test\Page\Adminhtml\OrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Signifyd Guarantee Status is present in Orders grid.
 */
class AssertSignifydCaseInOrdersGrid extends AbstractConstraint
{
    /**
     * Assert that order with fixture data is present in Sales -> Orders Grid.
     *
     * @param string $orderId
     * @param string $status
     * @param OrderView $orderView
     * @return void
     */
    public function processAssert(
        $orderId,
        $status,
        OrderView $orderView
    ) {
        $filter = [
            'id' => $orderId,
            'status' => $status,
            'signifyd_guarantee_status' => 'Approved'
        ];

        $errorMessage = implode(', ', $filter);

        $orderView->open();

        \PHPUnit_Framework_Assert::assertTrue(
            $orderView->getSalesOrderGrid()->isRowVisible(array_filter($filter)),
            'Order with following data \'' . $errorMessage . '\' is absent in Orders grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Signifyd case is present in sales orders grid.';
    }
}
