<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Hostedpro;

/**
 * Class \Magento\Paypal\Controller\Hostedpro\ReturnAction
 *
 * @since 2.0.0
 */
class ReturnAction extends \Magento\Framework\App\Action\Action
{
    /**
     * When a customer return to website from gateway.
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $session = $this->_objectManager->get(\Magento\Checkout\Model\Session::class);
        //TODO: some actions with order
        if ($session->getLastRealOrderId()) {
            $this->_redirect('checkout/onepage/success');
        }
    }
}
