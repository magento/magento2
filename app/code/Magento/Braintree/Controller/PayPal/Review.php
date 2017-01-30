<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\PayPal;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Model\PaymentMethod\PayPal;

class Review extends \Magento\Braintree\Controller\PayPal
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Braintree\Model\Config\PayPal $braintreePayPalConfig
     * @param \Magento\Paypal\Model\Config $paypalConfig
     * @param \Magento\Braintree\Model\CheckoutFactory $checkoutFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Braintree\Model\Config\PayPal $braintreePayPalConfig,
        \Magento\Paypal\Model\Config $paypalConfig,
        \Magento\Braintree\Model\CheckoutFactory $checkoutFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $braintreePayPalConfig,
            $paypalConfig,
            $checkoutFactory
        );
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return $this|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $paymentMethodNonce = $this->getRequest()->getParam('payment_method_nonce');
        $details = $this->getRequest()->getParam('details');
        if (!empty($details)) {
            $details = $this->jsonHelper->jsonDecode($details);
        }
        try {
            $this->initCheckout();

            if ($paymentMethodNonce && $details) {
                if (!$this->braintreePayPalConfig->isBillingAddressEnabled()) {
                    unset($details['billingAddress']);
                }
                $this->getCheckout()->initializeQuoteForReview($paymentMethodNonce, $details);
                $paymentMethod = $this->getQuote()->getPayment()->getMethodInstance();
                $paymentMethod->validate();
            } else {
                $paymentMethod = $this->getQuote()->getPayment()->getMethodInstance();
                if (!$paymentMethod || $paymentMethod->getCode() !== PayPal::METHOD_CODE) {
                    $this->messageManager->addErrorMessage(
                        __('Incorrect payment method.')
                    );

                    /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    return $resultRedirect->setPath('checkout/cart');
                }
                $this->getQuote()->setMayEditShippingMethod(true);
            }

            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            /** @var \Magento\Braintree\Block\Checkout\Review $reviewBlock */
            $reviewBlock = $resultPage->getLayout()->getBlock('braintree.paypal.review');
            $reviewBlock->setQuote($this->getQuote());
            $reviewBlock->getChildBlock('shipping_method')->setQuote($this->getQuote());
            return $resultPage;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t initialize checkout review.')
            );
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('checkout/cart');
    }
}
