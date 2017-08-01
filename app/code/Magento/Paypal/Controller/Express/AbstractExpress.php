<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Express;

use Magento\Checkout\Controller\Express\RedirectLoginInterface;
use Magento\Framework\App\Action\Action as AppAction;

/**
 * Abstract Express Checkout Controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
abstract class AbstractExpress extends AppAction implements RedirectLoginInterface
{
    /**
     * @var \Magento\Paypal\Model\Express\Checkout
     * @since 2.0.0
     */
    protected $_checkout;

    /**
     * Internal cache of checkout models
     *
     * @var array
     * @since 2.0.0
     */
    protected $_checkoutTypes = [];

    /**
     * @var \Magento\Paypal\Model\Config
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @var \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    protected $_quote = false;

    /**
     * Config mode type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_configType;

    /**
     * Config method type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_configMethod;

    /**
     * Checkout mode type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_checkoutType;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     * @since 2.0.0
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Paypal\Model\Express\Checkout\Factory
     * @since 2.0.0
     */
    protected $_checkoutFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     * @since 2.0.0
     */
    protected $_paypalSession;

    /**
     * @var \Magento\Framework\Url\Helper
     * @since 2.0.0
     */
    protected $_urlHelper;

    /**
     * @var \Magento\Customer\Model\Url
     * @since 2.0.0
     */
    protected $_customerUrl;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Framework\Session\Generic $paypalSession
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Framework\Session\Generic $paypalSession,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_checkoutFactory = $checkoutFactory;
        $this->_paypalSession = $paypalSession;
        $this->_urlHelper = $urlHelper;
        $this->_customerUrl = $customerUrl;
        parent::__construct($context);
        $parameters = ['params' => [$this->_configMethod]];
        $this->_config = $this->_objectManager->create($this->_configType, $parameters);
    }

    /**
     * Instantiate quote and checkout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _initCheckout()
    {
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t initialize Express Checkout.'));
        }
        if (!isset($this->_checkoutTypes[$this->_checkoutType])) {
            $parameters = [
                'params' => [
                    'quote' => $quote,
                    'config' => $this->_config,
                ],
            ];
            $this->_checkoutTypes[$this->_checkoutType] = $this->_checkoutFactory
                ->create($this->_checkoutType, $parameters);
        }
        $this->_checkout = $this->_checkoutTypes[$this->_checkoutType];
    }

    /**
     * Search for proper checkout token in request or session or (un)set specified one
     * Combined getter/setter
     *
     * @param string|null $setToken
     * @return $this|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _initToken($setToken = null)
    {
        if (null !== $setToken) {
            if (false === $setToken) {
                // security measure for avoid unsetting token twice
                if (!$this->_getSession()->getExpressCheckoutToken()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('PayPal Express Checkout Token does not exist.')
                    );
                }
                $this->_getSession()->unsExpressCheckoutToken();
            } else {
                $this->_getSession()->setExpressCheckoutToken($setToken);
            }
            return $this;
        }
        $setToken = $this->getRequest()->getParam('token');
        if ($setToken) {
            if ($setToken !== $this->_getSession()->getExpressCheckoutToken()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('A wrong PayPal Express Checkout Token is specified.')
                );
            }
        } else {
            $setToken = $this->_getSession()->getExpressCheckoutToken();
        }
        return $setToken;
    }

    /**
     * PayPal session instance getter
     *
     * @return \Magento\Framework\Session\Generic
     * @since 2.0.0
     */
    protected function _getSession()
    {
        return $this->_paypalSession;
    }

    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    protected function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Returns before_auth_url redirect parameter for customer session
     * @return null
     * @since 2.0.0
     */
    public function getCustomerBeforeAuthUrl()
    {
        return;
    }

    /**
     * Returns a list of action flags [flag_key] => boolean
     * @return array
     * @since 2.0.0
     */
    public function getActionFlagList()
    {
        return [];
    }

    /**
     * Returns login url parameter for redirect
     * @return string
     * @since 2.0.0
     */
    public function getLoginUrl()
    {
        return $this->_customerUrl->getLoginUrl();
    }

    /**
     * Returns action name which requires redirect
     * @return string
     * @since 2.0.0
     */
    public function getRedirectActionName()
    {
        return 'start';
    }

    /**
     * Redirect to login page
     *
     * @return void
     * @since 2.0.0
     */
    public function redirectLogin()
    {
        $this->_actionFlag->set('', 'no-dispatch', true);
        $this->_customerSession->setBeforeAuthUrl($this->_redirect->getRefererUrl());
        $this->getResponse()->setRedirect(
            $this->_urlHelper->addRequestParam($this->_customerUrl->getLoginUrl(), ['context' => 'checkout'])
        );
    }
}
