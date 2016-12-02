<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that comment about authorized amount exists in Comments History section on order page in Admin.
 */
class AssertAuthorizationInCommentsHistory extends AbstractConstraint
{
    /**
     * Pattern of message about authorized amount in order.
     */
    const AUTHORIZED_AMOUNT_PATTERN = '/([a-zA-Z\"\s]*)Authorized amount of \W+%S.([a-zA-Z0-9\"\s\:]*)/';

    /**
     * Assert that comment about authorized amount exist in Comments History section on order page in Admin.
     *
     * @param SalesOrderView $salesOrderView
     * @param string $orderId
     * @param array $prices
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        $orderId,
        array $prices
    ) {
        $salesOrderView->open(['order_id' => $orderId]);
        $actualAuthorizedAmount = $salesOrderView->getOrderHistoryBlock()->getAuthorizedAmount();

        \PHPUnit_Framework_Assert::assertRegExp(
            sprintf(self::AUTHORIZED_AMOUNT_PATTERN, $prices['grandTotal']),
            $actualAuthorizedAmount,
            'Incorrect authorized amount value for the order #' . $orderId
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Message about authorized amount is available in Comments History section.";
    }
}
