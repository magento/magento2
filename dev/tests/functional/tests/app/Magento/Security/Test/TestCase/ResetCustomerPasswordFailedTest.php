<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\ObjectManager;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountForgotPassword;

/**
 * Preconditions:
 * 1. Create customer.
 *
 * Steps:
 * 1. Open customer forgot password page.
 * 2. Perform password reset action specified number of times.
 * 3. Password reset failed message appears on each password reset attempt starting the second one.
 * 4. Perform all assertions.
 *
 * @ZephyrId MAGETWO-49027
 */
class ResetCustomerPasswordFailedTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Customer forgot password page.
     *
     * @var CustomerAccountForgotPassword
     */
    protected $forgotPassword;

    /**
     * Preparing pages for test.
     *
     * @param CustomerAccountForgotPassword $forgotPassword
     * @return void
     */
    public function __inject(
        CustomerAccountForgotPassword $forgotPassword
    ) {
        $this->forgotPassword = $forgotPassword;
    }

    /**
     * Run reset customer password failed test.
     * @param Customer $customer
     * @param int $attempts
     * @return void
     */
    public function test(
        Customer $customer,
        $attempts
    ) {
        // Steps
        $customer->persist();
        for ($i = 0; $i < $attempts; $i++) {
            $this->forgotPassword->open();
            $this->forgotPassword->getForgotPasswordForm()->resetForgotPassword($customer);
        }
    }
}
