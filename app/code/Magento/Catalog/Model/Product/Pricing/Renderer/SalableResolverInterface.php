<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Pricing\Renderer;

/**
 * Interface resolver checks whether product available for sale
 * @since 2.1.3
 */
interface SalableResolverInterface
{
    /**
     * Check whether product available for sale
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     * @return boolean
     * @since 2.1.3
     */
    public function isSalable(\Magento\Framework\Pricing\SaleableInterface $salableItem);
}
