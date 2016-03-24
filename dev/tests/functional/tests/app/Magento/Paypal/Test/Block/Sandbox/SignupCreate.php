<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Add address and create PayPal Sandbox account.
 */
class SignupCreate extends Form
{
    /**
     * Accept PayPal Agreement.
     *
     * @var string
     */
    protected $termsAgree = '#termsAgree';

    /**
     * Continue personal account signup button.
     *
     * @var string
     */
    protected $agreeAndCreateAccount = '#submitBtn';

    /**
     * Create PayPal Sandbox Account.
     *
     * @return void
     */
    public function createAccount()
    {
        $this->_rootElement->find($this->termsAgree, Locator::SELECTOR_CSS, 'checkbox')->setValue('Yes');
        $this->browser->selectWindow();
        $this->_rootElement->find($this->agreeAndCreateAccount)->click();
    }
}
