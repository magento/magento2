<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\TestCase;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

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
        $customer = Factory::getFixtureFactory()->getMagentoCustomerCustomer();
        $customer->switchData('customer_US_1');
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
