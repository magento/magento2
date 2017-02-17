<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Admin;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Login form for backend user.
 */
class Login extends Form
{
    /**
     * Fill login form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return void
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        if (!$fixture->getData('captcha')) {
            unset($this->mapping['captcha']);
        }
        parent::fill($fixture, $element);
    }

    /**
     * 'Log in' button.
     *
     * @var string
     */
    protected $submit = '.action-login';

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
     * Submit login form.
     */
    public function submit()
    {
        $this->_rootElement->find($this->submit, Locator::SELECTOR_CSS)->click();
    }

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

    /**
     * Wait for Login form is not visible in the page.
     *
     * @return void
     */
    public function waitFormNotVisible()
    {
        $form = $this->_rootElement;
        $this->browser->waitUntil(
            function () use ($form) {
                return $form->isVisible() ? null : true;
            }
        );
    }
}
