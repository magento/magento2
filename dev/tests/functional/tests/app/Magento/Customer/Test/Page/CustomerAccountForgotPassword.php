<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Page;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Page\Page;

/**
 * Customer forgot password page.
 */
class CustomerAccountForgotPassword extends Page
{
    /**
     * URL for reset customer password.
     */
    const MCA = 'customer/account/forgotpassword';

    /**
     * Forgot password form.
     *
     * @var string
     */
    protected $forgotPasswordForm = '#form-validate';

    /**
     * Init page. Set page url.
     *
     * @return void
     */
    protected function initUrl()
    {
        $this->url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Get Customer Forgot Password form.
     *
     * @return \Magento\Customer\Test\Block\Form\ForgotPassword
     */
    public function getForgotPasswordForm()
    {
        return Factory::getBlockFactory()->getMagentoCustomerFormForgotPassword(
            $this->browser->find(
                $this->forgotPasswordForm,
                Locator::SELECTOR_CSS
            )
        );
    }
}
