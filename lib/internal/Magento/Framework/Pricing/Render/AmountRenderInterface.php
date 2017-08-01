<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;

/**
 * Price amount renderer interface
 *
 * @api
 * @since 2.0.0
 */
interface AmountRenderInterface
{
    /**
     * Enforce custom display price value
     *
     * @param float $value
     * @return void
     * @since 2.0.0
     */
    public function setDisplayValue($value);

    /**
     * @return float
     * @since 2.0.0
     */
    public function getDisplayValue();

    /**
     * Retrieve amount object
     *
     * @return AmountInterface
     * @since 2.0.0
     */
    public function getAmount();

    /**
     * @return SaleableInterface
     * @since 2.0.0
     */
    public function getSaleableItem();

    /**
     * @return PriceInterface
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getDisplayCurrencyCode();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getDisplayCurrencySymbol();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAdjustmentsHtml();
}
