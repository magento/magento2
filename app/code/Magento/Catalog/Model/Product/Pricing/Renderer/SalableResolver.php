<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Pricing\Renderer;

/**
 * Resolvers checks whether product is available for sale or not
 */
class SalableResolver implements SalableResolverInterface
{
    /**
     * Check whether product is available for sale
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     * @return boolean
     */
    public function isSalable(\Magento\Framework\Pricing\SaleableInterface $salableItem)
    {
        return $salableItem->getCanShowPrice() !== false && $salableItem->isSalable();
    }
}
