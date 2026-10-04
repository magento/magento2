<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;

/**
 * Price amount renderer interface
 *
 * @api
 * @since 100.0.2
 */
interface AmountRenderInterface
{
    /**
     * Enforce custom display price value
     *
     * @param float $value
     * @return void
     */
    public function setDisplayValue($value);

    /**
     * @return float
     */
    public function getDisplayValue();

    /**
     * Retrieve amount object
     *
     * @return AmountInterface
     */
    public function getAmount();

    /**
     * @return SaleableInterface
     */
    public function getSaleableItem();

    /**
     * @return PriceInterface
     */
    public function getPrice();

    /**
     * @return string
     */
    public function getDisplayCurrencyCode();

    /**
     * @return string
     */
    public function getDisplayCurrencySymbol();

    /**
     * @return string
     */
    public function getAdjustmentsHtml();
}
