<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class ConfigurablePriceResolver implements PriceResolverInterface
{
    /** @var PriceResolverInterface */
    protected $priceResolver;

    /** @var PriceCurrencyInterface */
    protected $priceCurrency;

    /** @var Configurable */
    protected $configurable;

    /**
     * @param PriceResolverInterface $priceResolver
     * @param Configurable $configurable
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        PriceResolverInterface $priceResolver,
        Configurable $configurable,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceResolver = $priceResolver;
        $this->configurable = $configurable;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface|\Magento\Catalog\Model\Product $product
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        $price = null;
        foreach ($this->configurable->getUsedProducts($product) as $subProduct) {
            $productPrice = $this->priceResolver->resolvePrice($subProduct);
            $price = $price ? min($price, $productPrice) : $productPrice;
        }
        if (!$price) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Configurable product "%1" do not have sub-products', $product->getName())
            );
        }
        return (float)$price;
    }
}
