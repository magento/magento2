<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Product\Pricing\Renderer;

/**
 * Resolvers check whether product available for sale or not
 */
class SalableResolver implements SalableResolverInterface
{
    /**
     * Check whether product available for sale
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     * @return boolean
     */
    public function isSalable(\Magento\Framework\Pricing\SaleableInterface $salableItem)
    {
        return $salableItem->getCanShowPrice() !== false;
    }
}
