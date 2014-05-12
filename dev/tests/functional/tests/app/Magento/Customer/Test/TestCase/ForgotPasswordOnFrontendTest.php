<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *   
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
