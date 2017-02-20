<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Block\Form;

use Magento\Mtf\Client\Locator;
use Magento\Customer\Test\Block\Form\Login;

/**
 * Form for storefront login with captcha.
 */
class LoginWithCaptcha extends Login
{
    /**
     * Captcha image selector.
     *
     * @var string
     */
    private $captchaImage = '.captcha-img';

    /**
     * Captcha reload button selector.
     *
     * @var string
     */
    private $captchaReload = '.captcha-reload';

    /**
     * Get captcha element.
     *
     * @return ElementInterface
     */
    public function getCaptcha()
    {
        return $this->_rootElement->find($this->captchaImage, Locator::SELECTOR_CSS);
    }

    /**
     * Get captcha reload button element.
     *
     * @return ElementInterface
     */
    public function getCaptchaReloadButton()
    {
        return $this->_rootElement->find($this->captchaReload, Locator::SELECTOR_CSS);
    }
}
