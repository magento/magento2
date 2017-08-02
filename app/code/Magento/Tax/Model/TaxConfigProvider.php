<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class \Magento\Tax\Model\TaxConfigProvider
 *
 * @since 2.0.0
 */
class TaxConfigProvider implements ConfigProviderInterface
{
    /**
     * @var TaxHelper
     * @since 2.0.0
     */
    protected $taxHelper;

    /**
     * @var Config
     * @since 2.0.0
     */
    protected $taxConfig;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var CheckoutSession
     * @since 2.0.0
     */
    protected $checkoutSession;

    /**
     * @param TaxHelper $taxHelper
     * @param Config $taxConfig
     * @param CheckoutSession $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.0.0
     */
    public function __construct(
        TaxHelper $taxHelper,
        Config $taxConfig,
        CheckoutSession $checkoutSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->taxHelper = $taxHelper;
        $this->taxConfig = $taxConfig;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getConfig()
    {
        $defaultRegionId = $this->scopeConfig->getValue(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_REGION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
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
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            'defaultRegionId' => $defaultRegionId,
            'defaultPostcode' => $this->scopeConfig->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_POSTCODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
        ];
    }

    /**
     * Shipping mode: 'both', 'including', 'excluding'
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isDisplayShippingPriceExclTax()
    {
        return $this->taxHelper->displayShippingPriceExcludingTax();
    }

    /**
     * Return flag whether to display shipping price including and excluding tax
     *
     * @return bool
     * @since 2.0.0
     */
    public function isDisplayShippingBothPrices()
    {
        return $this->taxHelper->displayShippingBothPrices();
    }

    /**
     * Get review item price display mode
     *
     * @return string 'both', 'including', 'excluding'
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isFullTaxSummaryDisplayed()
    {
        return $this->taxHelper->displayFullSummary();
    }

    /**
     * Display tax in grand total section or not
     *
     * @return bool
     * @since 2.0.0
     */
    public function isTaxDisplayedInGrandTotal()
    {
        return $this->taxConfig->displayCartTaxWithGrandTotal();
    }

    /**
     * Reload totals(taxes) on billing address update
     *
     * @return bool
     * @since 2.0.0
     */
    protected function reloadOnBillingAddress()
    {
        $quote = $this->checkoutSession->getQuote();
        $configValue = $this->scopeConfig->getValue(
            Config::CONFIG_XML_PATH_BASED_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return 'billing' == $configValue || $quote->isVirtual();
    }
}
