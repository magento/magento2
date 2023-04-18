<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Tax\Model\Config as TaxConfig;

class TaxConfigProvider implements ConfigProviderInterface
{
    /**
     * @param TaxHelper $taxHelper
     * @param Config $taxConfig
     * @param CheckoutSession $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected readonly TaxHelper $taxHelper,
        protected readonly Config $taxConfig,
        protected readonly CheckoutSession $checkoutSession,
        protected readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $defaultRegionId = $this->scopeConfig->getValue(
            TaxConfig::CONFIG_XML_PATH_DEFAULT_REGION,
            ScopeInterface::SCOPE_STORE
        );
        // prevent wrong assignment on shipping rate estimation requests
        if (0 == $defaultRegionId) {
            $defaultRegionId = null;
        }
        return [
            'isDisplayShippingPriceExclTax' => $this->isDisplayShippingPriceExclTax(),
            'isDisplayShippingBothPrices' => $this->isDisplayShippingBothPrices(),
            'reviewShippingDisplayMode' => $this->getDisplayShippingMode(),
            'reviewItemPriceDisplayMode' => $this->getReviewItemPriceDisplayMode(),
            'reviewTotalsDisplayMode' => $this->getReviewTotalsDisplayMode(),
            'includeTaxInGrandTotal' => $this->isTaxDisplayedInGrandTotal(),
            'isFullTaxSummaryDisplayed' => $this->isFullTaxSummaryDisplayed(),
            'isZeroTaxDisplayed' => $this->taxConfig->displayCartZeroTax(),
            'reloadOnBillingAddress' => $this->reloadOnBillingAddress(),
            'defaultCountryId' => $this->scopeConfig->getValue(
                TaxConfig::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_STORE
            ),
            'defaultRegionId' => $defaultRegionId,
            'defaultPostcode' => $this->scopeConfig->getValue(
                TaxConfig::CONFIG_XML_PATH_DEFAULT_POSTCODE,
                ScopeInterface::SCOPE_STORE
            ),
        ];
    }

    /**
     * Shipping mode: 'both', 'including', 'excluding'
     *
     * @return string
     */
    public function getDisplayShippingMode()
    {
        if ($this->taxConfig->displayCartShippingBoth()) {
            return 'both';
        }
        if ($this->taxConfig->displayCartShippingExclTax()) {
            return 'excluding';
        }
        return 'including';
    }

    /**
     * Return flag whether to display shipping price excluding tax
     *
     * @return bool
     */
    public function isDisplayShippingPriceExclTax()
    {
        return $this->taxHelper->displayShippingPriceExcludingTax();
    }

    /**
     * Return flag whether to display shipping price including and excluding tax
     *
     * @return bool
     */
    public function isDisplayShippingBothPrices()
    {
        return $this->taxHelper->displayShippingBothPrices();
    }

    /**
     * Get review item price display mode
     *
     * @return string 'both', 'including', 'excluding'
     */
    public function getReviewItemPriceDisplayMode()
    {
        if ($this->taxHelper->displayCartBothPrices()) {
            return 'both';
        }
        if ($this->taxHelper->displayCartPriceExclTax()) {
            return 'excluding';
        }
        return 'including';
    }

    /**
     * Get review item price display mode
     *
     * @return string 'both', 'including', 'excluding'
     */
    public function getReviewTotalsDisplayMode()
    {
        if ($this->taxConfig->displayCartSubtotalBoth()) {
            return 'both';
        }
        if ($this->taxConfig->displayCartSubtotalExclTax()) {
            return 'excluding';
        }
        return 'including';
    }

    /**
     * Show tax details in checkout totals section flag
     *
     * @return bool
     */
    public function isFullTaxSummaryDisplayed()
    {
        return $this->taxHelper->displayFullSummary();
    }

    /**
     * Display tax in grand total section or not
     *
     * @return bool
     */
    public function isTaxDisplayedInGrandTotal()
    {
        return $this->taxConfig->displayCartTaxWithGrandTotal();
    }

    /**
     * Reload totals(taxes) on billing address update
     *
     * @return bool
     */
    protected function reloadOnBillingAddress()
    {
        $quote = $this->checkoutSession->getQuote();
        $configValue = $this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_BASED_ON,
            ScopeInterface::SCOPE_STORE
        );
        return 'billing' == $configValue || $quote->isVirtual();
    }
}
