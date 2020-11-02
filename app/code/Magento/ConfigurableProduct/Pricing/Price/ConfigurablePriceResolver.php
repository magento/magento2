<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * @inheritDoc
 */
class ConfigurablePriceResolver implements PriceResolverInterface
{
    /**
     * @var PriceResolverInterface
     * @deprecated
     */
    protected $priceResolver;

    /**
     * @var PriceCurrencyInterface
     * @deprecated 100.0.2
     */
    protected $priceCurrency;

    /**
     * @var Configurable
     * @deprecated 100.0.2
     */
    protected $configurable;

    /**
     * @param PriceResolverInterface $priceResolver
     * @param Configurable $configurable
     * @param PriceCurrencyInterface $priceCurrency
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
    }

    /**
     * @inheritDoc
     *
     * @param SaleableInterface|Product $product
     * @return float
     */
    public function resolvePrice(SaleableInterface $product)
    {
        return (float)$product->getMinimalPrice();
    }
}
