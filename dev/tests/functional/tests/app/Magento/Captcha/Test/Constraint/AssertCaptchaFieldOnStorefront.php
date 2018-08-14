<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert captcha on storefront login page.
 */
class AssertCaptchaFieldOnStorefront extends AbstractConstraint
{
    /**
     * Assert captcha and reload button are visible on storefront login page.
     *
     * @param CustomerAccountLogin $loginPage
     * @return void
     */
    public function processAssert(CustomerAccountLogin $loginPage)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            $loginPage->getLoginBlockWithCaptcha()->isVisibleCaptcha(),
            'Captcha image is not present on storefront login page.'
        );

        \PHPUnit_Framework_Assert::assertTrue(
            $loginPage->getLoginBlockWithCaptcha()->isVisibleCaptchaReloadButton(),
            'Captcha reload button is not present on storefront login page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Captcha and reload button are presents on storefront login page.';
    }
}
