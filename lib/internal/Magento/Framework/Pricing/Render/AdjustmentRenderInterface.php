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
 */
interface AdjustmentRenderInterface
{
    /**
     * @param AmountRenderInterface $amountRender
     * @param array $arguments
     * @return string
     */
    public function render(AmountRenderInterface $amountRender, array $arguments = []);

    /**
     * @return string
     */
    public function getAdjustmentCode();

    /**
     * @return array
     */
    public function getData();

    /**
     * (to use in templates only)
     *
     * @return AmountRenderInterface
     */
    public function getAmountRender();

    /**
     * (to use in templates only)
     *
     * @return PriceInterface
     */
    public function getPrice();

    /**
     * (to use in templates only)
     *
     * @return SaleableInterface
     */
    public function getSaleableItem();

    /**
     * (to use in templates only)
     *
     * @return \Magento\Framework\Pricing\Adjustment\AdjustmentInterface
     */
    public function getAdjustment();
}
