<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout\Address;

class EditBilling extends \Magento\Multishipping\Controller\Checkout\Address
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(
            \Magento\Multishipping\Model\Checkout\Type\Multishipping\State::STEP_BILLING
        );
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        if ($addressForm = $this->_view->getLayout()->getBlock('customer_address_edit')) {
            $id = $this->getRequest()->getParam('id');
            $addressForm->setTitle(
                __('Edit Billing Address')
            )->setSuccessUrl(
                $this->_url->getUrl('*/*/saveBilling', ['id' => $id])
            )->setErrorUrl(
                $this->_url->getUrl('*/*/*', ['id' => $id])
            )->setBackUrl(
                $this->_url->getUrl('*/checkout/overview')
            );
            $this->_view->getPage()->getConfig()->getTitle()->set(
                $addressForm->getTitle() . ' - ' . $this->_view->getPage()->getConfig()->getTitle()->getDefault()
            );
        }
        $this->_view->renderLayout();
    }
}
