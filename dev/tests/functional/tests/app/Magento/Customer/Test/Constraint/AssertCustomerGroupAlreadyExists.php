<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerGroupNew;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerGroupAlreadyExists
 */
class AssertCustomerGroupAlreadyExists extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const ERROR_MESSAGE = 'Customer Group already exists.';

    /**
     * Assert that customer group already exist
     *
     * @param CustomerGroupNew $customerGroupNew
     * @return void
     */
    public function processAssert(CustomerGroupNew $customerGroupNew)
    {
        $actualMessage = $customerGroupNew->getMessagesBlock()->getErrorMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_MESSAGE,
            $actualMessage,
            'Wrong error message is displayed.'
        );
    }

    /**
     * Success assert of customer group already exist
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group already exist.';
    }
}
