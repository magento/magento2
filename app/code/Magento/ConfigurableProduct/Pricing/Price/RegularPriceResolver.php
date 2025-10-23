<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

class RegularPriceResolver implements PriceResolverInterface
{
    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $product
     * @return float
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        return $product->getPrice();
    }
}
