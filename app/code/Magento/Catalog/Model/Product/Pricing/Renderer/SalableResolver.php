<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Pricing\Renderer;

class SalableResolver implements SalableResolverInterface
{
    /**
     * Check is product available for sale
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     * @return boolean
     */
    public function isSalable($salableItem)
    {
        if (!$salableItem ||
            $salableItem->getCanShowPrice() === false ||
            !$salableItem->isSalable()
        ) {
            return false;
        }

        return true;
    }
}
