<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Sandbox;

use Magento\Mtf\Block\Block;

/**
 * Choose PayPal Sandbox account type on signup block.
 */
class SignupChooseAccountType extends Block
{
    /**
     * Personal account selector.
     *
     * @var string
     */
    protected $personalAccount = '[value="Personal"]';

    /**
     * Continue personal signup button.
     *
     * @var string
     */
    protected $continue = '.personalSignUpForm';

    /**
     * Select personal account for signup.
     *
     * @return void
     */
    public function selectPersonalAccount()
    {
        $this->_rootElement->find($this->personalAccount)->click();
        $this->_rootElement->find($this->continue)->click();
    }
}
