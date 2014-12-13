<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Multishipping\Controller\Checkout;

class Register extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Multishipping checkout login page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $this->getResponse()->setRedirect($this->_getHelper()->getMSCheckoutUrl());
            return;
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();

        $registerForm = $this->_view->getLayout()->getBlock('customer_form_register');
        if ($registerForm) {
            $registerForm->setShowAddressFields(
                true
            )->setBackUrl(
                $this->_getHelper()->getMSLoginUrl()
            )->setSuccessUrl(
                $this->_getHelper()->getMSShippingAddressSavedUrl()
            )->setErrorUrl(
                $this->_url->getCurrentUrl()
            );
        }

        $this->_view->renderLayout();
    }
}
