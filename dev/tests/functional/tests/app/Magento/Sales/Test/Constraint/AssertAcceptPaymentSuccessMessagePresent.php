<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Class AssertAcceptPaymentSuccessMessagePresent
 *
 * Constraint checks success message on the order page
 * after accepting order payment
 */
class AssertAcceptPaymentSuccessMessagePresent extends AbstractConstraint
{
    /**
     * @var string
     */
    private static $successAcceptMessage = 'The payment has been accepted.';

    /**
     * Assert that success message present after accept payment
     *
     * @param SalesOrderView $salesOrderView
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::$successAcceptMessage,
            $salesOrderView->getMessagesBlock()->getSuccessMessage()
        );
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Success accept payment message is present.';
    }
}
