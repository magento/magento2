<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller;

use Magento\Framework\App\RequestInterface;

/**
 * Braintree PayPal Checkout Controller
 */
abstract class PayPal extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Braintree\Model\CheckoutFactory
     */
    protected $checkoutFactory;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote = false;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Braintree\Model\Config\PayPal
     */
    protected $braintreePayPalConfig;

    /**
     * @var \Magento\Paypal\Model\Config
     */
    protected $paypalConfig;

    /**
     * @var \Magento\Braintree\Model\Checkout
     */
    protected $checkout;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Braintree\Model\Config\PayPal $braintreePayPalConfig
     * @param \Magento\Paypal\Model\Config $paypalConfig
     * @param \Magento\Braintree\Model\CheckoutFactory $checkoutFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Braintree\Model\Config\PayPal $braintreePayPalConfig,
        \Magento\Paypal\Model\Config $paypalConfig,
        \Magento\Braintree\Model\CheckoutFactory $checkoutFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->braintreePayPalConfig = $braintreePayPalConfig;
        $this->paypalConfig = $paypalConfig;
        $this->checkoutFactory = $checkoutFactory;
    }

    /**
     * Check whether payment method is enabled
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->braintreePayPalConfig->isActive() || !$this->braintreePayPalConfig->isShortcutCheckoutEnabled()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('noRoute');
            return $resultRedirect;
        }

        return parent::dispatch($request);
    }

    /**
     * Instantiate quote and checkout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initCheckout()
    {
        $quote = $this->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t initialize checkout.'));
        }
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * @return \Magento\Braintree\Model\Checkout
     */
    protected function getCheckout()
    {
        if (!$this->checkout) {
            $this->checkout = $this->checkoutFactory->create(
                [
                    'params' => [
                        'quote' => $this->checkoutSession->getQuote(),
                        'config' => $this->paypalConfig,
                    ],
                ]
            );
        }
        return $this->checkout;
    }
}
