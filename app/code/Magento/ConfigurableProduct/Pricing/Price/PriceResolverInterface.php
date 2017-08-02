<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

/**
 * @api
 * @since 2.0.0
 */
interface PriceResolverInterface
{
    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $product
     * @return float
     * @since 2.0.0
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product);
}
