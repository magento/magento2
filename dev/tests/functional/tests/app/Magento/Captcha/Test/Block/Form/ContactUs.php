<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Block\Form;

use Magento\Contact\Test\Block\Form\ContactUs as ContactForm;
use Magento\Mtf\Client\Locator;

/**
 * Form for "Contact Us" page with captcha.
 */
class ContactUs extends ContactForm
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
     * Click captcha reload button element.
     *
     * @return void
     */
    public function reloadCaptcha()
    {
        $this->_rootElement->find($this->captchaReload, Locator::SELECTOR_CSS)->click();
    }
}
