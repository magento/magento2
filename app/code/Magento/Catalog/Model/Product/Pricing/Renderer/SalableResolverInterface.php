<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Product\Pricing\Renderer;

/**
 * Interface resolver checks whether product available for sale
 *
 * @api
 */
interface SalableResolverInterface
{
    /**
     * Check whether product available for sale
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     * @return boolean
     */
    public function isSalable(\Magento\Framework\Pricing\SaleableInterface $salableItem);
}
