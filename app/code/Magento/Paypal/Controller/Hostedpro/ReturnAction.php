<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Hostedpro;

class ReturnAction extends \Magento\Framework\App\Action\Action
{
    /**
     * When a customer return to website from gateway.
     *
     * @return void
     */
    public function execute()
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        //TODO: some actions with order
        if ($session->getLastRealOrderId()) {
            $this->_redirect('checkout/onepage/success');
        }
    }
}
