<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;

/**
 * Login to PayPal within old login page.
 */
class ExpressOldLogin extends Form
{
    /**
     * Login button.
     *
     * @var string
     */
    protected $loginButton = '#submitLogin';

    /**
     * Login to PayPal Sandbox.
     *
     * @return void
     */
    public function sandboxLogin()
    {
        $this->_rootElement->find($this->loginButton)->click();
    }
}
