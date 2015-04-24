<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Tax\Helper\Data as TaxHelper;

class TaxConfigProvider implements ConfigProviderInterface
{
    /**
     * @var TaxHelper
     */
    protected $taxHelper;

    /**
     * @var Config
     */
    protected $taxConfig;

    /**
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        TaxHelper $taxHelper,
        Config $taxConfig
    ) {
        $this->taxHelper = $taxHelper;
        $this->taxConfig = $taxConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'isDisplayShippingPriceExclTax' => $this->isDisplayShippingPriceExclTax(),
            'isDisplayShippingBothPrices' => $this->isDisplayShippingBothPrices(),
            'reviewItemPriceDisplayMode' => $this->getReviewItemPriceDisplayMode(),
            'reviewTotalsDisplayMode' => $this->getReviewTotalsDisplayMode(),
            'includeTaxInGrandTotal' => $this->getIncludeTaxInGrandTotal(),
        ];
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
     * Display tax in grand total section or not
     *
     * @return bool
     */
    public function getIncludeTaxInGrandTotal()
    {
        return $this->taxConfig->displayCartTaxWithGrandTotal();
    }
}
