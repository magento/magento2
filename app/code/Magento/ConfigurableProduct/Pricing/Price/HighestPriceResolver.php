<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

/**
 * Resolves the highest price of a configurable product.
 */
class HighestPriceResolver implements PriceResolverInterface
{
    /**
     * @var HighestPriceOptionsProvider
     */
    private $highestPriceOptionsProvider;

    /**
     * @param HighestPriceOptionsProvider $highestPriceOptionsProvider
     */
    public function __construct(
        HighestPriceOptionsProvider $highestPriceOptionsProvider
    ) {
        $this->highestPriceOptionsProvider = $highestPriceOptionsProvider;
    }

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $product
     *
     * @return float
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        $price = null;

        /** @var \Magento\Catalog\Model\Product $subProduct */
        foreach ($this->highestPriceOptionsProvider->getProducts($product->getId()) as $subProduct) {
            $productPrice = $subProduct->getPriceInfo()->getPrice(
                FinalPrice::PRICE_CODE
            )->getValue();

            $price = $price ? max($price, $productPrice) : $productPrice;
        }

        return (float) $price;
    }
}
