<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\PayPal;

use Magento\Framework\Controller\ResultFactory;

class PlaceOrder extends \Magento\Braintree\Controller\PayPal
{
    /**
     * Submit the order
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->initCheckout();
            $this->getCheckout()->place(null);

            // prepare session to success or cancellation page
            $this->checkoutSession->clearHelperData();

            // "last successful quote"
            $quoteId = $this->getQuote()->getId();
            $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->getCheckout()->getOrder();
            if ($order) {
                $this->checkoutSession->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
            }

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('checkout/onepage/success');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t place the order.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('checkout/cart');
    }
}
