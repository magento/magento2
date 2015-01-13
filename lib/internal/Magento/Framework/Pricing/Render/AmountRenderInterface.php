<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Object\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;

/**
 * Price amount renderer interface
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
    public function getAdjustmentsHtml();
}
