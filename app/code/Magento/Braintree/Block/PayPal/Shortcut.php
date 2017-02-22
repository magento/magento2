<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\PayPal;

use Magento\Catalog\Block as CatalogBlock;
use Magento\Braintree\Model\PaymentMethod\PayPal as PayPalPaymentMethod;
use Magento\Braintree\Model\Config\PayPal as PayPalConfig;

/**
 * Braintree Paypal express checkout shortcut link
 *
 */
class Shortcut extends \Magento\Framework\View\Element\Template implements CatalogBlock\ShortcutInterface
{
    /**
     * Key for the flag of mini cart
     */
    const MINI_CART_FLAG_KEY = 'is_in_mini_cart';

    /**
     * Default template name
     */
    const PAYPAL_SHORTCUT_TEMPLATE = 'PayPal/shortcut.phtml';

    /**
     * Shortcut alias
     *
     * @var string
     */
    protected $alias = '';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var PayPalConfig
     */
    protected $paypalConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param PayPalConfig $paypalConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        PayPalConfig $paypalConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mathRandom = $mathRandom;
        $this->localeResolver = $localeResolver;
        $this->paypalConfig = $paypalConfig;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->checkoutData = $checkoutData;
        $this->setTemplate(self::PAYPAL_SHORTCUT_TEMPLATE);
    }

    /**
     * @return bool
     */
    public function isInMiniCart()
    {
        return (bool)$this->getData(self::MINI_CART_FLAG_KEY);
    }

    /**
     * @return string
     */
    public function getButtonDataUrl()
    {
        return $this->_urlBuilder->getUrl('braintree/paypal/getbuttondata');
    }

    /**
     * Get shortcut alias
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getClientToken()
    {
        return $this->paypalConfig->getClientToken();
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->checkoutSession->getQuote()->getBaseGrandTotal();
    }

    /**
     * @return string
     */
    public function getReviewPageUrl()
    {
        return $this->_urlBuilder->getUrl('braintree/paypal/review', ['_secure' => true]);
    }

    /**
     * @return null|string
     */
    public function getCurrency()
    {
        return $this->checkoutSession->getQuote()->getCurrency()->getBaseCurrencyCode();
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->paypalConfig->getMerchantNameOverride();
    }

    /**
     * @return bool
     */
    public function enableBillingAddress()
    {
        return $this->paypalConfig->isBillingAddressEnabled();
    }

    /**
     * Don't display the shortcut button if customer is not logged in and guest mode is not allowed
     *
     * @return bool
     */
    public function skipShortcutForGuest()
    {
        if ($this->customerSession->isLoggedIn()) {
            return false;
        }
        if ($this->checkoutData->isAllowedGuestCheckout($this->checkoutSession->getQuote())) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        if ($this->isInMiniCart()) {
            return 'braintree_paypal_container_minicart';
        } else {
            return 'braintree_paypal_container' . $this->mathRandom->getRandomString(5);
        }
    }

    /**
     * @return string
     */
    public function getSubmitFormId()
    {
        if ($this->isInMiniCart()) {
            return 'braintree_paypal_submit_form_minicart';
        } else {
            return 'braintree_paypal_submit_form' . $this->mathRandom->getRandomString(5);
        }
    }

    /**
     * @return string
     */
    public function getPaymentMethodNonceId()
    {
        if ($this->isInMiniCart()) {
            return 'braintree_paypal_payment_method_nonce_minicart';
        } else {
            return 'braintree_paypal_payment_method_nonce' . $this->mathRandom->getRandomString(5);
        }
    }

    /**
     * @return string
     */
    public function getPaymentDetailsId()
    {
        if ($this->isInMiniCart()) {
            return 'braintree_paypal_payment_details_minicart';
        } else {
            return 'braintree_paypal_payment_details' . $this->mathRandom->getRandomString(5);
        }
    }
}
