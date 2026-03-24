<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Multishipping\Controller\Checkout\Address;

class SetBilling extends \Magento\Multishipping\Controller\Checkout\Address
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($addressId = $this->getRequest()->getParam('id')) {
            $this->_objectManager->create(
                \Magento\Multishipping\Model\Checkout\Type\Multishipping::class
            )->setQuoteCustomerBillingAddress(
                $addressId
            );
        }
        $this->_redirect('*/checkout/billing');
    }
}
