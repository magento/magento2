<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Express\AbstractExpress;

use Magento\Paypal\Model\Api\ProcessableException as ApiProcessableException;

class PlaceOrder extends \Magento\Paypal\Controller\Express\AbstractExpress
{
    /**
     * Submit the order
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $agreementsValidator = $this->_objectManager->get('Magento\Checkout\Model\Agreements\AgreementsValidator');
            if (!$agreementsValidator->isValid(array_keys($this->getRequest()->getPost('agreement', [])))) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please agree to all the terms and conditions before placing the order.')
                );
            }

            $this->_initCheckout();
            $this->_checkout->place($this->_initToken());

            // prepare session to success or cancellation page
            $this->_getCheckoutSession()->clearHelperData();

            // "last successful quote"
            $quoteId = $this->_getQuote()->getId();
            $this->_getCheckoutSession()->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->_checkout->getOrder();
            if ($order) {
                $this->_getCheckoutSession()->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());
            }

            $this->_eventManager->dispatch(
                'paypal_express_place_order_success',
                [
                    'order' => $order,
                    'quote' => $this->_getQuote()
                ]
            );

            // redirect if PayPal specified some URL (for example, to Giropay bank)
            $url = $this->_checkout->getRedirectUrl();
            if ($url) {
                $this->getResponse()->setRedirect($url);
                return;
            }
            $this->_initToken(false); // no need in token anymore
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (ApiProcessableException $e) {
            $this->_processPaypalApiError($e);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/review');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t place the order.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->_redirect('*/*/review');
        }
    }

    /**
     * Process PayPal API's processable errors
     *
     * @param \Magento\Paypal\Model\Api\ProcessableException $exception
     * @return void
     */
    protected function _processPaypalApiError($exception)
    {
        switch ($exception->getCode()) {
            case ApiProcessableException::API_MAX_PAYMENT_ATTEMPTS_EXCEEDED:
            case ApiProcessableException::API_TRANSACTION_EXPIRED:
                $this->getResponse()->setRedirect(
                    $this->_getQuote()->getPayment()->getCheckoutRedirectUrl()
                );
                break;
            case ApiProcessableException::API_DO_EXPRESS_CHECKOUT_FAIL:
                $this->_redirectSameToken();
                break;
            case ApiProcessableException::API_UNABLE_TRANSACTION_COMPLETE:
                if ($this->_config->getPaymentAction() == \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER) {
                    $paypalTransactionData = $this->_getCheckoutSession()->getPaypalTransactionData();
                    $this->getResponse()->setRedirect(
                        $this->_config->getExpressCheckoutOrderUrl($paypalTransactionData['transaction_id'])
                    );
                } else {
                    $this->_redirectSameToken();
                }
                break;
            default:
                $this->_redirectToCartAndShowError($exception->getUserMessage());
                break;
        }
    }

    /**
     * Redirect customer back to PayPal with the same token
     *
     * @return void
     */
    protected function _redirectSameToken()
    {
        $token = $this->_initToken();
        $this->getResponse()->setRedirect(
            $this->_config->getExpressCheckoutStartUrl($token)
        );
    }

    /**
     * Redirect customer to shopping cart and show error message
     *
     * @param string $errorMessage
     * @return void
     */
    protected function _redirectToCartAndShowError($errorMessage)
    {
        $this->messageManager->addError($errorMessage);
        $this->_redirect('checkout/cart');
    }
}
