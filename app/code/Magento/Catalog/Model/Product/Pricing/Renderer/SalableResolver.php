<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Pricing\Renderer;

/**
 * Resolvers check whether product available for sale or not
 * @since 2.1.3
 */
class SalableResolver implements SalableResolverInterface
{
    /**
     * Check whether product available for sale
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     * @return boolean
     * @since 2.1.3
     */
    public function isSalable(\Magento\Framework\Pricing\SaleableInterface $salableItem)
    {
        return $salableItem->getCanShowPrice() !== false;
    }
}
