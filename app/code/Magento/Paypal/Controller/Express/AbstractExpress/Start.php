<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Express\AbstractExpress;

use Magento\Checkout\Model\Type\Onepage;

class Start extends \Magento\Paypal\Controller\Express\AbstractExpress
{
    /**
     * Start Express Checkout by requesting initial token and dispatching customer to PayPal
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        try {
            $this->_initCheckout();

            if ($this->_getQuote()->getIsMultiShipping()) {
                $this->_getQuote()->setIsMultiShipping(false);
                $this->_getQuote()->removeAllAddresses();
            }

            $customerData = $this->_customerSession->getCustomerDataObject();
            $quoteCheckoutMethod = $this->_getQuote()->getCheckoutMethod();
            if ($customerData->getId()) {
                $this->_checkout->setCustomerWithAddressChange(
                    $customerData,
                    $this->_getQuote()->getBillingAddress(),
                    $this->_getQuote()->getShippingAddress()
                );
            } elseif ((!$quoteCheckoutMethod || $quoteCheckoutMethod != Onepage::METHOD_REGISTER)
                && !$this->_objectManager->get('Magento\Checkout\Helper\Data')->isAllowedGuestCheckout(
                    $this->_getQuote(),
                    $this->_getQuote()->getStoreId()
                )
            ) {
                $this->messageManager->addNoticeMessage(
                    __('To check out, please sign in with your email address.')
                );

                $this->_objectManager->get('Magento\Checkout\Helper\ExpressRedirect')->redirectLogin($this);
                $this->_customerSession->setBeforeAuthUrl($this->_url->getUrl('*/*/*', ['_current' => true]));

                return;
            }

            // billing agreement
            $isBaRequested = (bool)$this->getRequest()
                ->getParam(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
            if ($customerData->getId()) {
                $this->_checkout->setIsBillingAgreementRequested($isBaRequested);
            }

            // Bill Me Later
            $this->_checkout->setIsBml((bool)$this->getRequest()->getParam('bml'));

            // giropay
            $this->_checkout->prepareGiropayUrls(
                $this->_url->getUrl('checkout/onepage/success'),
                $this->_url->getUrl('paypal/express/cancel'),
                $this->_url->getUrl('checkout/onepage/success')
            );

            $button = (bool)$this->getRequest()->getParam(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_BUTTON);
            $token = $this->_checkout->start(
                $this->_url->getUrl('*/*/return'),
                $this->_url->getUrl('*/*/cancel'),
                $button
            );
            $url = $this->_checkout->getRedirectUrl();
            if ($token && $url) {
                $this->_initToken($token);
                $this->getResponse()->setRedirect($url);
                return;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t start Express Checkout.')
            );
        }

        $this->_redirect('checkout/cart');
    }
}
