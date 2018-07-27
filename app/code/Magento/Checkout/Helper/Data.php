<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Api\PaymentFailuresInterface;

/**
 * Checkout default helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GUEST_CHECKOUT = 'checkout/options/guest_checkout';

    const XML_PATH_CUSTOMER_MUST_BE_LOGGED = 'checkout/options/customer_must_be_logged';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var PaymentFailuresInterface
     */
    private $paymentFailures;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param PriceCurrencyInterface $priceCurrency
     * @param PaymentFailuresInterface|null $paymentFailures
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        PriceCurrencyInterface $priceCurrency,
        PaymentFailuresInterface $paymentFailures = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_localeDate = $localeDate;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->priceCurrency = $priceCurrency;
        $this->paymentFailures = $paymentFailures ? : \Magento\Framework\App\ObjectManager::getInstance()
            ->get(PaymentFailuresInterface::class);
        parent::__construct($context);
    }

    /**
     * Retrieve checkout session model
     *
     * @return \Magento\Checkout\Model\Session
     * @codeCoverageIgnore
     */
    public function getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Retrieve checkout quote model object
     *
     * @return \Magento\Quote\Model\Quote
     * @codeCoverageIgnore
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }

    /**
     * @param float $price
     * @param bool $format
     * @return float
     */
    public function convertPrice($price, $format = true)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat($price)
            : $this->priceCurrency->convert($price);
    }

    /**
     * Get onepage checkout availability
     *
     * @return bool
     */
    public function canOnepageCheckout()
    {
        return (bool)$this->scopeConfig->getValue(
            'checkout/options/onepage_checkout_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get sales item (quote item, order item etc) price including tax based on row total and tax amount
     *
     * @param   \Magento\Framework\DataObject $item
     * @return  float
     */
    public function getPriceInclTax($item)
    {
        if ($item->getPriceInclTax()) {
            return $item->getPriceInclTax();
        }
        $qty = $item->getQty() ? $item->getQty() : ($item->getQtyOrdered() ? $item->getQtyOrdered() : 1);
        $taxAmount = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
        $price = floatval($qty) ? ($item->getRowTotal() + $taxAmount) / $qty : 0;
        return $this->priceCurrency->round($price);
    }

    /**
     * Get sales item (quote item, order item etc) row total price including tax
     *
     * @param   \Magento\Framework\DataObject $item
     * @return  float
     */
    public function getSubtotalInclTax($item)
    {
        if ($item->getRowTotalInclTax()) {
            return $item->getRowTotalInclTax();
        }
        $tax = $item->getTaxAmount() + $item->getDiscountTaxCompensation();
        return $item->getRowTotal() + $tax;
    }

    /**
     * @param AbstractItem $item
     * @return float
     */
    public function getBasePriceInclTax($item)
    {
        $qty = $item->getQty() ? $item->getQty() : ($item->getQtyOrdered() ? $item->getQtyOrdered() : 1);
        $taxAmount = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
        $price = floatval($qty) ? ($item->getBaseRowTotal() + $taxAmount) / $qty : 0;
        return $this->priceCurrency->round($price);
    }

    /**
     * @param AbstractItem $item
     * @return float
     */
    public function getBaseSubtotalInclTax($item)
    {
        $tax = $item->getBaseTaxAmount() + $item->getBaseDiscountTaxCompensation();
        return $item->getBaseRowTotal() + $tax;
    }

    /**
     * Send email id payment was failed
     *
     * @param \Magento\Quote\Model\Quote $checkout
     * @param string $message
     * @param string $checkoutType
     * @return $this
     */
    public function sendPaymentFailedEmail($checkout, $message, $checkoutType = 'onepage')
    {
        $this->paymentFailures->handle($checkout->getId(), $message, $checkoutType);

        return $this;
    }

    /**
     * @param string $configPath
     * @param null|string|bool|int|Store $storeId
     * @return array|false
     */
    protected function _getEmails($configPath, $storeId)
    {
        $data = $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    /**
     * Check is allowed Guest Checkout
     * Use config settings and observer
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param int|Store $store
     * @return bool
     */
    public function isAllowedGuestCheckout(\Magento\Quote\Model\Quote $quote, $store = null)
    {
        if ($store === null) {
            $store = $quote->getStoreId();
        }
        $guestCheckout = $this->scopeConfig->isSetFlag(
            self::XML_PATH_GUEST_CHECKOUT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($guestCheckout == true) {
            $result = new \Magento\Framework\DataObject();
            $result->setIsAllowed($guestCheckout);
            $this->_eventManager->dispatch(
                'checkout_allow_guest',
                ['quote' => $quote, 'store' => $store, 'result' => $result]
            );

            $guestCheckout = $result->getIsAllowed();
        }

        return $guestCheckout;
    }

    /**
     * Check if context is checkout
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isContextCheckout()
    {
        return $this->_request->getParam('context') == 'checkout';
    }

    /**
     * Check if user must be logged during checkout process
     *
     * @return boolean
     * @codeCoverageIgnore
     */
    public function isCustomerMustBeLogged()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CUSTOMER_MUST_BE_LOGGED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Checks if display billing address on payment method is available, otherwise
     * billing address should be display on payment page
     * @return bool
     */
    public function isDisplayBillingOnPaymentMethodAvailable()
    {
        return (bool) !$this->scopeConfig->getValue(
            'checkout/options/display_billing_address_on',
            ScopeInterface::SCOPE_STORE
        );
    }
}
