<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Account;

class Login extends \Magento\Customer\Controller\Account
{
    /**
     * Customer login form page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $this->getResponse()->setHeader('Login-Required', 'true');
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
