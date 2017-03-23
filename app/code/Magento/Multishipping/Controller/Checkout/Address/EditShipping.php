<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout\Address;

class EditShipping extends \Magento\Multishipping\Controller\Checkout\Address
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(
            \Magento\Multishipping\Model\Checkout\Type\Multishipping\State::STEP_SHIPPING
        );
        $this->_view->loadLayout();
        if ($addressForm = $this->_view->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(
                __('Edit Shipping Address')
            )->setSuccessUrl(
                $this->_url->getUrl('*/*/editShippingPost', ['id' => $this->getRequest()->getParam('id')])
            )->setErrorUrl(
                $this->_url->getUrl('*/*/*')
            );

            $this->_view->getPage()->getConfig()->getTitle()->set(
                $addressForm->getTitle() . ' - ' . $this->_view->getPage()->getConfig()->getTitle()->getDefault()
            );

            if ($this->_getCheckout()->getCustomerDefaultShippingAddress()) {
                $addressForm->setBackUrl($this->_url->getUrl('*/checkout/shipping'));
            }
        }
        $this->_view->renderLayout();
    }
}
