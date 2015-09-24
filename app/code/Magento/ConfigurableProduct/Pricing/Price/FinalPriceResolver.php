<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

class FinalPriceResolver implements PriceResolverInterface
{
    /**
     * @param \Magento\Framework\Pricing\Object\SaleableInterface $product
     * @return float
     */
    public function resolvePrice(\Magento\Framework\Pricing\Object\SaleableInterface $product)
    {
        return $product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
            ->getAmount()->getValue();
    }
}
