<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Block\Account;

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
