<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;

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
     * Switch to the form frame and fill form. {@inheritdoc}
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        parent::fill($fixture, $element);
        return $this;
    }

    /**
     * Login to Authorize.Net Sandbox.
     *
     * @return void
     */
    public function login()
    {
        $this->_rootElement->find($this->loginButton)->click();
    }
}
