<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
