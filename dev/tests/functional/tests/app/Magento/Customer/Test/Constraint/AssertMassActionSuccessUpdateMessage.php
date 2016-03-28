<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertMassActionSuccessUpdateMessage
 * Assert update message is appears on customer grid (Customers > All Customers)
 */
class AssertMassActionSuccessUpdateMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Text value to be checked
     */
    const UPDATE_MESSAGE = 'A total of %d record(s) were updated.';

    /**
     * Assert update message is appears on customer grid (Customers > All Customers)
     *
     * @param Customer|Customer[] $customer
     * @param CustomerIndex $pageCustomerIndex
     * @return void
     */
    public function processAssert($customer, CustomerIndex $pageCustomerIndex)
    {
        $customers = is_array($customer) ? $customer : [$customer];
        $actualMessage = $pageCustomerIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(sprintf(self::UPDATE_MESSAGE, count($customers)), $actualMessage);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that update message is displayed.';
    }
}
