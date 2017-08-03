<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;

/**
 * Class \Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver
 *
 * @since 2.0.0
 */
class FinalPriceResolver implements PriceResolverInterface
{
    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $product
     * @return float
     * @since 2.0.0
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        return $product->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE)->getValue();
    }
}
