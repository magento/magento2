<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Class AssertDenyPaymentSuccessMessagePresent
 *
 * Constraint checks success message on the order page
 * after denying order payment
 */
class AssertDenyPaymentSuccessMessagePresent extends AbstractConstraint
{
    /**
     * @var string
     */
    private static $successDenyMessage = 'The payment has been denied.';

    /**
     * Assert that success message present after deny payment
     *
     * @param SalesOrderView $salesOrderView
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::$successDenyMessage,
            $salesOrderView->getMessagesBlock()->getSuccessMessage()
        );
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Success deny payment message is present.';
    }
}
