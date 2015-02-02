<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Mtf\Factory\Factory;
use Magento\Mtf\TestCase\Functional;

/**
 * Reset password on frontend
 */
class ForgotPasswordOnFrontendTest extends Functional
{
    /**
     * Reset password on frontend
     */
    public function testForgotPassword()
    {
        // Create Customer
        $customer = $this->objectManager->getInstance()->create(
            'Magento\Customer\Test\Fixture\Customer',
            ['dataSet' => 'customer_US_1']
        );
        $customer->persist();

        $customerAccountLoginPage = Factory::getPageFactory()->getCustomerAccountLogin();
        $forgotPasswordPage = Factory::getPageFactory()->getCustomerAccountForgotpassword();
        $forgotPasswordPage->open();

        $forgotPasswordPage->getForgotPasswordForm()->resetForgotPassword($customer);

        //Verifying
        $message = sprintf(
            'If there is an account associated with %s you will receive an email with a link to reset your password.',
            $customer->getEmail()
        );
        $this->assertContains($message, $customerAccountLoginPage->getMessages()->getSuccessMessages());
    }
}
