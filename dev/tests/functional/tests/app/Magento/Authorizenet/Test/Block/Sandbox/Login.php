<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\ObjectManager;

/**
 * Login block.
 */
class Login extends Form
{
    /**
     * Login button on Authorize.Net Sandbox.
     *
     * @var string
     */
    private $loginButton = '[type=submit]';

    /**
     * Form frame selector.
     *
     * @var string
     */
    private $frame = 'frameset > frame';

    /**
     * Switch to the form frame and fill form. {@inheritdoc}
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return void
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $this->browser->switchToFrame($this->_rootElement->find($this->frame)->getLocator());
        parent::fill($fixture, $element);
    }

    /**
     * Login to Authorize.Net Sandbox.
     *
     * @return void
     */
    public function sandboxLogin()
    {
        $this->_rootElement->find($this->loginButton)->click();
    }
}
