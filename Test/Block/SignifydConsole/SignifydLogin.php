<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\SignifydConsole;

use Magento\Mtf\Block\Form;

/**
 * Signifyd login block.
 */
class SignifydLogin extends Form
{
    /**
     * Css selector of Signifyd login button.
     *
     * @var string
     */
    private $loginButton = '[type=submit]';

    /**
     * Login to Signifyd.
     *
     * @return void
     */
    public function login()
    {
        $this->_rootElement->find($this->loginButton)->click();
    }
}
