<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Pricing\Render;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Weee\Model\Tax;

/**
 * Weee Price Adjustment that overrides part of the Tax module's Adjustment
 */
class TaxAdjustment extends \Magento\Tax\Pricing\Render\Adjustment
{
    /**
     * Weee helper
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeHelper;

    /**
     * @param Template\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Helper\Data $helper
     * @param \Magento\Weee\Helper\Data $weeeHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $helper,
        \Magento\Weee\Helper\Data $weeeHelper,
        array $data = []
    ) {
        $this->weeeHelper = $weeeHelper;
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
        $exclusions[] = \Magento\Weee\Pricing\TaxAdjustment::ADJUSTMENT_CODE;

        // Determine if the Weee amount should be excluded from the price
        if ($this->typeOfDisplay([Tax::DISPLAY_EXCL_DESCR_INCL, Tax::DISPLAY_EXCL])) {
            $exclusions[] = \Magento\Weee\Pricing\Adjustment::ADJUSTMENT_CODE;
        }

        return $exclusions;
    }

    /**
     * Returns display type for price accordingly to current zone
     *
     * @param int|int[]|null $compareTo
     * @param \Magento\Store\Model\Store|null $store
     * @return bool|int
     */
    protected function typeOfDisplay($compareTo = null, $store = null)
    {
        return $this->weeeHelper->typeOfDisplay($compareTo, $this->getZone(), $store);
    }
}
