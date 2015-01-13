<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;

/**
 * Checkout default helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GUEST_CHECKOUT = 'checkout/options/guest_checkout';

    const XML_PATH_CUSTOMER_MUST_BE_LOGGED = 'checkout/options/customer_must_be_logged';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

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
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_localeDate = $localeDate;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    /**
     * Retrieve checkout session model
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Retrieve checkout quote model object
     *
     * @return \Magento\Sales\Model\Quote
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
        return (bool)$this->_scopeConfig->getValue(
            'checkout/options/onepage_checkout_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get sales item (quote item, order item etc) price including tax based on row total and tax amount
     *
     * @param   \Magento\Framework\Object $item
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
     * @param   \Magento\Framework\Object $item
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
     * @param \Magento\Sales\Model\Quote $checkout
     * @param string $message
     * @param string $checkoutType
     * @return $this
     */
    public function sendPaymentFailedEmail($checkout, $message, $checkoutType = 'onepage')
    {
        $this->inlineTranslation->suspend();

        $template = $this->_scopeConfig->getValue(
            'checkout/payment_failed/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $checkout->getStoreId()
        );

        $copyTo = $this->_getEmails('checkout/payment_failed/copy_to', $checkout->getStoreId());
        $copyMethod = $this->_scopeConfig->getValue(
            'checkout/payment_failed/copy_method',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $checkout->getStoreId()
        );
        $bcc = [];
        if ($copyTo && $copyMethod == 'bcc') {
            $bcc = $copyTo;
        }

        $_receiver = $this->_scopeConfig->getValue(
            'checkout/payment_failed/receiver',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $checkout->getStoreId()
        );
        $sendTo = [
            [
                'email' => $this->_scopeConfig->getValue(
                    'trans_email/ident_' . $_receiver . '/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $checkout->getStoreId()
                ),
                'name' => $this->_scopeConfig->getValue(
                    'trans_email/ident_' . $_receiver . '/name',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $checkout->getStoreId()
                ),
            ],
        ];

        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = ['email' => $email, 'name' => null];
            }
        }
        $shippingMethod = '';
        if ($shippingInfo = $checkout->getShippingAddress()->getShippingMethod()) {
            $data = explode('_', $shippingInfo);
            $shippingMethod = $data[0];
        }

        $paymentMethod = '';
        if ($paymentInfo = $checkout->getPayment()) {
            $paymentMethod = $paymentInfo->getMethod();
        }

        $items = '';
        foreach ($checkout->getAllVisibleItems() as $_item) {
            /* @var $_item \Magento\Sales\Model\Quote\Item */
            $items .=
                $_item->getProduct()->getName() . '  x ' . $_item->getQty() . '  ' . $checkout->getStoreCurrencyCode()
                . ' ' . $_item->getProduct()->getFinalPrice(
                    $_item->getQty()
                ) . "\n";
        }
        $total = $checkout->getStoreCurrencyCode() . ' ' . $checkout->getGrandTotal();

        foreach ($sendTo as $recipient) {
            $transport = $this->_transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $checkout->getStoreId()]
            )->setTemplateVars(
                [
                    'reason' => $message,
                    'checkoutType' => $checkoutType,
                    'dateAndTime' => $this->_localeDate->date(),
                    'customer' => $checkout->getCustomerFirstname() . ' ' . $checkout->getCustomerLastname(),
                    'customerEmail' => $checkout->getCustomerEmail(),
                    'billingAddress' => $checkout->getBillingAddress(),
                    'shippingAddress' => $checkout->getShippingAddress(),
                    'shippingMethod' => $this->_scopeConfig->getValue(
                        'carriers/' . $shippingMethod . '/title',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                    'paymentMethod' => $this->_scopeConfig->getValue(
                        'payment/' . $paymentMethod . '/title',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                    'items' => nl2br($items),
                    'total' => $total,
                ]
            )->setFrom(
                $this->_scopeConfig->getValue(
                    'checkout/payment_failed/identity',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $checkout->getStoreId()
                )
            )->addTo(
                $recipient['email'],
                $recipient['name']
            )->addBcc(
                $bcc
            )->getTransport();

            $transport->sendMessage();
        }

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * @param string $configPath
     * @param null|string|bool|int|Store $storeId
     * @return array|false
     */
    protected function _getEmails($configPath, $storeId)
    {
        $data = $this->_scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    /**
     * Check is allowed Guest Checkout
     * Use config settings and observer
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @param int|Store $store
     * @return bool
     */
    public function isAllowedGuestCheckout(\Magento\Sales\Model\Quote $quote, $store = null)
    {
        if ($store === null) {
            $store = $quote->getStoreId();
        }
        $guestCheckout = $this->_scopeConfig->isSetFlag(
            self::XML_PATH_GUEST_CHECKOUT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($guestCheckout == true) {
            $result = new \Magento\Framework\Object();
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
     */
    public function isContextCheckout()
    {
        return $this->_request->getParam('context') == 'checkout';
    }

    /**
     * Check if user must be logged during checkout process
     *
     * @return boolean
     */
    public function isCustomerMustBeLogged()
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_CUSTOMER_MUST_BE_LOGGED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
