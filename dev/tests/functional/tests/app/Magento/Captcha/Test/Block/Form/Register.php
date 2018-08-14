<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Block\Form;

use Magento\Customer\Test\Block\Form\Register as CustomerRegister;
use Magento\Mtf\Client\Locator;

/**
 * Form for storefront register customer with captcha.
 */
class Register extends CustomerRegister
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
     * Get captcha element visibility.
     *
     * @return bool
     */
    public function isVisibleCaptcha()
    {
        return $this->_rootElement->find($this->captchaImage, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Get captcha reload button element visibility.
     *
     * @return bool
     */
    public function isVisibleCaptchaReloadButton()
    {
        return $this->_rootElement->find($this->captchaReload, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Click on captcha reload button.
     *
     * @return void
     */
    public function reloadCaptcha()
    {
        $this->_rootElement->find($this->captchaReload, Locator::SELECTOR_CSS)->click();
    }
}
