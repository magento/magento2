<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Pricing\Price\FinalPrice;

class FinalPriceResolver implements PriceResolverInterface
{
    /**
     * @param \Magento\Framework\Pricing\Object\SaleableInterface $product
     * @return float
     */
    public function getPrice(\Magento\Framework\Pricing\Object\SaleableInterface $product)
    {
        return $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount()->getValue();
    }
}
