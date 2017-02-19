<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Block\Admin;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Backend\Test\Block\Admin\Login;

/**
 * Login form for backend user.
 */
class LoginWithCaptcha extends Login
{
    /**
     * Captcha image selector.
     *
     * @var string
     */
    private $captchaImage = '#backend_login';

    /**
     * Captcha reload button selector.
     *
     * @var string
     */
    private $captchaReload = '#captcha-reload';

    /**
     * Return captcha element.
     *
     * @return SimpleElement
     */
    public function getCaptcha()
    {
        return $this->_rootElement->find($this->captchaImage, Locator::SELECTOR_CSS);
    }

    /**
     * Return captcha reload button element.
     *
     * @return SimpleElement
     */
    public function getCaptchaReloadButton()
    {
        return $this->_rootElement->find($this->captchaReload, Locator::SELECTOR_CSS);
    }
}
