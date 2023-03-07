<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Pricing\Render;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\Store;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Pricing\Render\Adjustment as PricingRenderAdjustment;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Model\Tax;
use Magento\Weee\Pricing\Adjustment as PricingAdjustment;
use Magento\Weee\Pricing\TaxAdjustment as PricingTaxAdjestment;

/**
 * Weee Price Adjustment that overrides part of the Tax module's Adjustment
 */
class TaxAdjustment extends PricingRenderAdjustment
{
    /**
     * @param Template\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param TaxHelper $helper
     * @param WeeeHelper $weeeHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        TaxHelper $helper,
        protected WeeeHelper $weeeHelper,
        array $data = []
    ) {
        parent::__construct($context, $priceCurrency, $helper, $data);
    }

    /**
     * Returns the list of default exclusions
     *
     * @return array
     */
    public function getDefaultExclusions()
    {
        $exclusions = parent::getDefaultExclusions();
        $exclusions[] = PricingTaxAdjestment::ADJUSTMENT_CODE;

        // Determine if the Weee amount should be excluded from the price
        if ($this->typeOfDisplay([Tax::DISPLAY_EXCL_DESCR_INCL, Tax::DISPLAY_EXCL])) {
            $exclusions[] = PricingAdjustment::ADJUSTMENT_CODE;
        }

        return $exclusions;
    }

    /**
     * Returns display type for price accordingly to current zone
     *
     * @param int|int[]|null $compareTo
     * @param Store|null $store
     * @return bool|int
     */
    protected function typeOfDisplay($compareTo = null, $store = null)
    {
        return $this->weeeHelper->typeOfDisplay($compareTo, $this->getZone(), $store);
    }
}
