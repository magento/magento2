<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;

/**
 * Add Card to PayPal Sandbox Account.
 */
class SignupAddCard extends Form
{
    /**
     * Link card to PayPal account button.
     *
     * @var string
     */
    protected $linkCard = '#submitBtn';

    /**
     * Link card to PayPal Sandbox Account.
     *
     * @return void
     */
    public function linkCardToAccount()
    {
        $this->_rootElement->find($this->linkCard)->click();
    }
}
