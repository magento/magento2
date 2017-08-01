<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Hostedpro;

/**
 * Class \Magento\Paypal\Controller\Hostedpro\Redirect
 *
 * @since 2.0.0
 */
class Redirect extends \Magento\Paypal\Controller\Payflow
{
    /**
     * Redirect to HostedPro gateway into iframe
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getOrder()
    {
        return $this->_orderFactory->create()->loadByIncrementId(
            $this->_checkoutSession->getLastRealOrderId()
        );
    }
}
