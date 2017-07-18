<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Block\Account;

use Magento\Mtf\Block\Form;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Authentication wrapper block.
 */
class AuthenticationWrapper extends AuthenticationPopup
{
    /**
     * 'Sign In' link.
     *
     * @var string
     */
    protected $signInLink = '[data-trigger="authentication"]';

    /**
     * Click on 'Sign In' link.
     *
     * @return void
     */
    public function signInLinkClick()
    {
        $this->_rootElement->find($this->signInLink)->click();
    }
}
