<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver
 *
 * @since 2.0.0
 */
class ConfigurablePriceResolver implements PriceResolverInterface
{
    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface
     * @since 2.0.0
     */
    protected $priceResolver;

    /**
     * @var PriceCurrencyInterface
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @var Configurable
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $configurable;

    /**
     * @var LowestPriceOptionsProviderInterface
     * @since 2.2.0
     */
    private $lowestPriceOptionsProvider;

    /**
     * @param PriceResolverInterface $priceResolver
     * @param Configurable $configurable
     * @param PriceCurrencyInterface $priceCurrency
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
     * @since 2.0.0
     */
    public function __construct(
        PriceResolverInterface $priceResolver,
        Configurable $configurable,
        PriceCurrencyInterface $priceCurrency,
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider = null
    ) {
        $this->priceResolver = $priceResolver;
        $this->configurable = $configurable;
        $this->priceCurrency = $priceCurrency;
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider ?:
            ObjectManager::getInstance()->get(LowestPriceOptionsProviderInterface::class);
    }

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface|\Magento\Catalog\Model\Product $product
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        $price = null;

        foreach ($this->lowestPriceOptionsProvider->getProducts($product) as $subProduct) {
            $productPrice = $this->priceResolver->resolvePrice($subProduct);
            $price = $price ? min($price, $productPrice) : $productPrice;
        }

        return (float)$price;
    }
}
