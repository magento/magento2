<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Controller\Hostedpro;

class Redirect extends \Magento\Paypal\Controller\Payflow
{
    /**
     * Redirect to HostedPro gateway into iframe
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setRedirect(
            $this->getOrder()->getPayment()->getAdditionalInformation('secure_form_url')
        );
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        return $this->_orderFactory->create()->loadByIncrementId(
            $this->_checkoutSession->getLastRealOrderId()
        );
    }
}
