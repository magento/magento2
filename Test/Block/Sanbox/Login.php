<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Sanbox;

use Magento\Mtf\Block\Form;

class Login extends Form
{
    /**
     * Login button on Signifyd side.
     *
     * @var string
     */
    protected $loginButton = '[type=submit]';

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
