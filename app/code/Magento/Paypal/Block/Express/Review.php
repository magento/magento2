<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Block\Express;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address\Rate;

/**
 * Paypal Express Onepage checkout block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Review extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var \Magento\Quote\Model\Quote\Address
     */
    protected $_address;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * Currently selected shipping rate
     *
     * @var Rate
     */
    protected $_currentShippingRate = null;

    /**
     * Paypal controller path
     *
     * @var string
     */
    protected $_controllerPath = 'paypal/express';

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_taxHelper = $taxHelper;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context, $data);
    }

    /**
     * Quote object setter
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * Return quote billing address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getBillingAddress()
    {
        return $this->_quote->getBillingAddress();
    }

    /**
     * Return quote shipping address
     *
     * @return false|\Magento\Quote\Model\Quote\Address
     */
    public function getShippingAddress()
    {
        if ($this->_quote->getIsVirtual()) {
            return false;
        }
        return $this->_quote->getShippingAddress();
    }

    /**
     * Get HTML output for specified address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return string
     */
    public function renderAddress($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        $addressData = \Magento\Framework\Convert\ConvertArray::toFlatArray($address->getData());
        return $renderer->renderArray($addressData);
    }

    /**
     * Return carrier name from config, base on carrier code
     *
     * @param string $carrierCode
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = $this->_scopeConfig->getValue("carriers/{$carrierCode}/title", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * Get either shipping rate code or empty value on error
     *
     * @param \Magento\Framework\DataObject $rate
     * @return string
     */
    public function renderShippingRateValue(\Magento\Framework\DataObject $rate)
    {
        if ($rate->getErrorMessage()) {
            return '';
        }
        return $rate->getCode();
    }

    /**
     * Get shipping rate code title and its price or error message
     *
     * @param \Magento\Framework\DataObject $rate
     * @param string $format
     * @param string $inclTaxFormat
     * @return string
     */
    public function renderShippingRateOption($rate, $format = '%s - %s%s', $inclTaxFormat = ' (%s %s)')
    {
        $renderedInclTax = '';
        if ($rate->getErrorMessage()) {
            $price = $rate->getErrorMessage();
        } else {
            $price = $this->_getShippingPrice(
                $rate->getPrice(),
                $this->_taxHelper->displayShippingPriceIncludingTax()
            );

            $incl = $this->_getShippingPrice($rate->getPrice(), true);
            if ($incl != $price && $this->_taxHelper->displayShippingBothPrices()) {
                $renderedInclTax = sprintf($inclTaxFormat, $this->escapeHtml(__('Incl. Tax')), $incl);
            }
        }
        return sprintf($format, $this->escapeHtml($rate->getMethodTitle()), $price, $renderedInclTax);
    }

    /**
     * Getter for current shipping rate
     *
     * @return Rate
     */
    public function getCurrentShippingRate()
    {
        return $this->_currentShippingRate;
    }

    /**
     * Whether can edit shipping method
     *
     * @return bool
     */
    public function canEditShippingMethod()
    {
        return $this->getData('can_edit_shipping_method') || !$this->getCurrentShippingRate();
    }

    /**
     * Get quote email
     *
     * @return string
     */
    public function getEmail()
    {
        $billingAddress = $this->getBillingAddress();
        return $billingAddress ? $billingAddress->getEmail() : '';
    }

    /**
     * Set controller path
     *
     * @param string $prefix
     * @return void
     */
    public function setControllerPath($prefix)
    {
        $this->_controllerPath = $prefix;
    }

    /**
     * Return formatted shipping price
     *
     * @param float $price
     * @param bool $isInclTax
     * @return string
     */
    protected function _getShippingPrice($price, $isInclTax)
    {
        return $this->_formatPrice($this->_taxHelper->getShippingPrice($price, $isInclTax, $this->_address));
    }

    /**
     * Format price base on store convert price method
     *
     * @param float $price
     * @return string
     */
    protected function _formatPrice($price)
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->_quote->getStore()
        );
    }

    /**
     * Retrieve payment method and assign additional template values
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _beforeToHtml()
    {
        $methodInstance = $this->_quote->getPayment()->getMethodInstance();
        $this->setPaymentMethodTitle($methodInstance->getTitle());

        $this->setShippingRateRequired(true);
        if ($this->_quote->getIsVirtual()) {
            $this->setShippingRateRequired(false);
        } else {
            // prepare shipping rates
            $this->_address = $this->_quote->getShippingAddress();
            $groups = $this->_address->getGroupedAllShippingRates();
            if ($groups && $this->_address) {
                $this->setShippingRateGroups($groups);
                // determine current selected code & name
                foreach ($groups as $code => $rates) {
                    foreach ($rates as $rate) {
                        if ($this->_address->getShippingMethod() == $rate->getCode()) {
                            $this->_currentShippingRate = $rate;
                            break 2;
                        }
                    }
                }
            }

            $canEditShippingAddress = $this->_quote->getMayEditShippingAddress() && $this->_quote->getPayment()
                ->getAdditionalInformation(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_BUTTON) == 1;
            // misc shipping parameters
            $this->setShippingMethodSubmitUrl(
                $this->getUrl("{$this->_controllerPath}/saveShippingMethod", ['_secure' => true])
            )->setCanEditShippingAddress(
                $canEditShippingAddress
            )->setCanEditShippingMethod(
                $this->_quote->getMayEditShippingMethod()
            );
        }

        $this->setEditUrl(
            $this->getUrl("{$this->_controllerPath}/edit")
        )->setPlaceOrderUrl(
            $this->getUrl("{$this->_controllerPath}/placeOrder", ['_secure' => true])
        );

        return parent::_beforeToHtml();
    }
}
