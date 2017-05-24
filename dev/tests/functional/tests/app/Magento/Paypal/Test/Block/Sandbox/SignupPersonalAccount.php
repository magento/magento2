<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;

/**
 * Signup for PayPal Sandbox account with specific email and password block.
 */
class SignupPersonalAccount extends Form
{
    /**
     * Continue personal account signup button.
     *
     * @var string
     */
    protected $continuePersonal = '#_eventId_personal';

    /**
     * Continue PayPal Sandbox Account Signup.
     *
     * @return void
     */
    public function continueSignup()
    {
        $this->_rootElement->find($this->continuePersonal)->click();
    }
}
