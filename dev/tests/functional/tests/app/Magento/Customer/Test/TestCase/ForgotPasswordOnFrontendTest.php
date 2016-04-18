<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountForgotPassword;

/**
 * Precondition:
 * 1. Customer is created.
 *
 * Steps:
 * 1. Open forgot password page.
 * 2. Fill email.
 * 3. Click forgot password button.
 * 4. Check forgot password message.
 *
 * @group Customer_(CS)
 * @ZephyrId MAGETWO-37145
 */
class ForgotPasswordOnFrontendTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Create customer.
     *
     * @param Customer $customer
     * @param CustomerAccountForgotPassword $forgotPassword
     * @return void
     */
    public function test(Customer $customer, CustomerAccountForgotPassword $forgotPassword)
    {
        // Precondition
        $customer->persist();

        // Steps
        $forgotPassword->open();
        $forgotPassword->getForgotPasswordForm()->resetForgotPassword($customer);
    }
}
