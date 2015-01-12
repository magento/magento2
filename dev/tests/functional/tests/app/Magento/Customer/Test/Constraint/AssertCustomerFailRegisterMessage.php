<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountCreate;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerFailRegisterMessage
 */
class AssertCustomerFailRegisterMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that error message is displayed on "Create New Customer Account" page(frontend)
     *
     * @param CustomerAccountCreate $registerPage
     * @return void
     */
    public function processAssert(CustomerAccountCreate $registerPage)
    {
        $errorMessage = $registerPage->getMessagesBlock()->getErrorMessages();
        \PHPUnit_Framework_Assert::assertNotEmpty(
            $errorMessage,
            'No error message is displayed.'
        );
    }

    /**
     * Text error message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that error message is displayed.';
    }
}
