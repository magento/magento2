<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;

/**
 * Signifyd login block.
 */
class SignifydLogin extends Form
{
    /**
     * Login button on Signifyd side.
     *
     * @var string
     */
    private $loginButton = '[type=submit]';

    /**
     * Login to Signifyd Sandbox.
     *
     * @return void
     */
    public function sandboxLogin()
    {
        $this->_rootElement->find($this->loginButton)->click();
    }
}
