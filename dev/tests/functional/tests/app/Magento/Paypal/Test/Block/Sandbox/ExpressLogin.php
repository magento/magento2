<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;

/**
 * Login to PayPal.
 */
class ExpressLogin extends Form
{
    /**
     * Login button on PayPal side.
     *
     * @var string
     */
    protected $loginButton = '#btnLogin';

    /**
     * PayPal load spinner.
     *
     * @var string
     */
    protected $preloaderSpinner = '#preloaderSpinner';

    /**
     * Wait for PayPal page is loaded.
     *
     * @return void
     */
    public function waitForFormLoaded()
    {
        $this->waitForElementNotVisible($this->preloaderSpinner);
    }

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
