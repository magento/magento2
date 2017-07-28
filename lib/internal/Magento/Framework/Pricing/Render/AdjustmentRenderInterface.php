<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;

/**
 * Adjustment render interface
 *
 * @api
 * @since 2.0.0
 */
interface AdjustmentRenderInterface
{
    /**
     * @param AmountRenderInterface $amountRender
     * @param array $arguments
     * @return string
     * @since 2.0.0
     */
    public function render(AmountRenderInterface $amountRender, array $arguments = []);

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAdjustmentCode();

    /**
     * @return array
     * @since 2.0.0
     */
    public function getData();

    /**
     * (to use in templates only)
     *
     * @return AmountRenderInterface
     * @since 2.0.0
     */
    public function getAmountRender();

    /**
     * (to use in templates only)
     *
     * @return PriceInterface
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * (to use in templates only)
     *
     * @return SaleableInterface
     * @since 2.0.0
     */
    public function getSaleableItem();

    /**
     * (to use in templates only)
     *
     * @return \Magento\Framework\Pricing\Adjustment\AdjustmentInterface
     * @since 2.0.0
     */
    public function getAdjustment();
}
