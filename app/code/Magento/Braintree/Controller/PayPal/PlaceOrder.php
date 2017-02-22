<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller\PayPal;

use Magento\Framework\Controller\ResultFactory;

class PlaceOrder extends \Magento\Braintree\Controller\PayPal
{
    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface
     */
    protected $agreementsValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Braintree\Model\Config\PayPal $braintreePayPalConfig
     * @param \Magento\Paypal\Model\Config $paypalConfig
     * @param \Magento\Braintree\Model\CheckoutFactory $checkoutFactory
     * @param \Magento\Checkout\Api\AgreementsValidatorInterface $agreementsValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Braintree\Model\Config\PayPal $braintreePayPalConfig,
        \Magento\Paypal\Model\Config $paypalConfig,
        \Magento\Braintree\Model\CheckoutFactory $checkoutFactory,
        \Magento\Checkout\Api\AgreementsValidatorInterface $agreementsValidator
    ) {
        $this->agreementsValidator = $agreementsValidator;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $braintreePayPalConfig,
            $paypalConfig,
            $checkoutFactory
        );
    }

    /**
     * Submit the order
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            if (!$this->agreementsValidator->isValid(array_keys($this->getRequest()->getPost('agreement', [])))) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please agree to all the terms and conditions before placing the order.')
                );
            }
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
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('checkout/cart');
    }
}
