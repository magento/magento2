<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Controller\Standard;

use Magento\Sales\Model\Order;

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * When a customer cancel payment from paypal.
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Checkout\Model\Session $session */
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));

        if ($session->getLastRealOrderId()) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->_objectManager->create(
                'Magento\Sales\Model\Order'
            )->loadByIncrementId(
                $session->getLastRealOrderId()
            );
            if ($order->getId()) {
                $order->cancel()->save();
            }
            $session->restoreQuote();
        }
        $this->_redirect('checkout/cart');
    }
}
