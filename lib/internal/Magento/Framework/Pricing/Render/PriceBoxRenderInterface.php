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
 * Price box render interface
 *
 * @api
 */
interface PriceBoxRenderInterface
{
    /**
     * @return SaleableInterface
     */
    public function getSaleableItem();

    /**
     * Retrieve price object
     * (to use in templates only)
     *
     * @return PriceInterface
     */
    public function getPrice();

    /**
     * Retrieve amount html for given price and arguments
     * (to use in templates only)
     *
     * @param AmountInterface $price
     * @param array $arguments
     * @return string
     */
    public function renderAmount(AmountInterface $price, array $arguments = []);
}
