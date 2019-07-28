<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountForgotPassword;
use Magento\Mtf\TestCase\Injectable;

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
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Customer forgot password page.
     *
     * @var CustomerAccountForgotPassword
     */
    protected $forgotPassword;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

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
     * @param string $configData
     * @return void
     */
    public function test(
        Customer $customer,
        $attempts,
        $configData = null
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        // Steps
        $customer->persist();
        for ($i = 0; $i < $attempts; $i++) {
            $this->forgotPassword->open();
            $this->forgotPassword->getForgotPasswordForm()->resetForgotPassword($customer);
        }
    }
}
