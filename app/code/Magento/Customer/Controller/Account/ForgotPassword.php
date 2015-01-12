<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Account;

class ForgotPassword extends \Magento\Customer\Controller\Account
{
    /**
     * Forgot customer password page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();

        $this->_view->getLayout()->getBlock(
            'forgotPassword'
        )->setEmailValue(
            $this->_getSession()->getForgottenEmail()
        );
        $this->_getSession()->unsForgottenEmail();

        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
