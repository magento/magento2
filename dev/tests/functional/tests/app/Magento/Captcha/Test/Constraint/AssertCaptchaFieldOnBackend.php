<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Constraint;

use Magento\Captcha\Test\Page\Captcha\AdminAuthLoginWithCaptcha;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert captcha on backend login page.
 */
class AssertCaptchaFieldOnBackend extends AbstractConstraint
{
    /**
     * Assert captcha and reload button are visible on backend login page.
     *
     * @param AdminAuthLoginWithCaptcha $adminAuthLogin
     * @return void
     */
    public function processAssert(AdminAuthLoginWithCaptcha $adminAuthLogin)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $adminAuthLogin->getLoginBlockWithCaptcha()->isVisibleCaptcha(),
            'Captcha image is not present on backend login page.'
        );

        \PHPUnit\Framework\Assert::assertTrue(
            $adminAuthLogin->getLoginBlockWithCaptcha()->isVisibleCaptchaReloadButton(),
            'Captcha reload button is not present on backend login page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Captcha and reload button are presents on backend login page.';
    }
}
