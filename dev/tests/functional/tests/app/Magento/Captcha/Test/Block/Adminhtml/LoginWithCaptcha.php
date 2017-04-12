<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\Admin\Login;
use Magento\Mtf\Client\Locator;

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
     * Return captcha element visibility.
     *
     * @return bool
     */
    public function isVisibleCaptcha()
    {
        return $this->_rootElement->find($this->captchaImage, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Return captcha reload button element visibility.
     *
     * @return bool
     */
    public function isVisibleCaptchaReloadButton()
    {
        return $this->_rootElement->find($this->captchaReload, Locator::SELECTOR_CSS)->isVisible();
    }
}
