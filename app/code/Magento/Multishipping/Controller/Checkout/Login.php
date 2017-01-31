<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

class Login extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Multishipping checkout login page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        $this->_view->loadLayout();

        // set account create url
        $loginForm = $this->_view->getLayout()->getBlock('customer.new');
        if ($loginForm) {
            $loginForm->setCreateAccountUrl($this->_getHelper()->getMSRegisterUrl());
        }
        $this->_view->renderLayout();
    }
}
