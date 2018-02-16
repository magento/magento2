<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Pricing\Renderer;

/**
 * Resolver provided to check is product available for sale
 */
class SalableResolver implements SalableResolverInterface
{
    /**
     * Check is product available for sale
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     * @return boolean
     */
    public function isSalable(\Magento\Framework\Pricing\SaleableInterface $salableItem)
    {
        return $salableItem->getCanShowPrice() !== false && $salableItem->isSalable();
    }
}
