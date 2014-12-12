<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
