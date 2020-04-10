<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;

class ConfigurablePriceResolver implements PriceResolverInterface
{
    /**
     * @var PriceResolverInterface
     */
    protected $priceResolver;

    /**
     * @var PriceCurrencyInterface
     * @deprecated 100.1.1
     */
    protected $priceCurrency;

    /**
     * @var Configurable
     * @deprecated 100.1.1
     */
    protected $configurable;

    /**
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    /**
     * @param PriceResolverInterface $priceResolver
     * @param Configurable $configurable
     * @param PriceCurrencyInterface $priceCurrency
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
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
     * Returns minimal price
     *
     * @param SaleableInterface|Product $product
     * @return float
     */
    public function resolvePrice(SaleableInterface $product): float
    {
        $price = null;

        foreach ($this->lowestPriceOptionsProvider->getProducts($product) as $subProduct) {
            $productPrice = $this->priceResolver->resolvePrice($subProduct);
            $price = isset($price) ? min($price, $productPrice) : $productPrice;
        }

        if ($this->hasTierPrice($product)) {
            $tierPrice = $this->getMinimalTierPrice($product);
            $price = isset($price) ? min($price, $tierPrice) : $price;
        }

        return (float)$price;
    }

    /**
     * Check if at least one child product has tier price
     *
     * @param SaleableInterface|Product $product
     * @return bool
     */
    private function hasTierPrice(SaleableInterface $product): bool
    {
        /** @var ProductInterface $subProduct */
        foreach ($this->getChildProducts($product) as $subProduct) {
            $tierPriceList = $this->getTierPriceList($subProduct);
            if (!empty($tierPriceList)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns tier price list for product
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getTierPriceList(ProductInterface $product): array
    {
        $productInfo = $product->getPriceInfo();
        /** @var TierPrice $tierPrice*/
        $tierPrice = $productInfo->getPrice(TierPrice::PRICE_CODE);

        return $tierPrice->getTierPriceList();
    }

    /**
     * Returns children products for configurable
     *
     * @param SaleableInterface|Product $product
     * @return ProductInterface[]
     */
    private function getChildProducts(SaleableInterface $product): array
    {
        $configurableProduct = $product->getTypeInstance();
        if ($configurableProduct instanceof Configurable) {
            return $configurableProduct->getUsedProducts($product);
        }

        return [];
    }

    /**
     * Provides minimal tier price
     *
     * @param SaleableInterface|Product $product
     * @return float
     */
    private function getMinimalTierPrice(SaleableInterface $product): float
    {
        $tierPrices = [];
        foreach ($this->getChildProducts($product) as $subProduct) {
            $tierPriceList = $this->getTierPriceList($subProduct);
            if (!empty($tierPriceList)) {
                foreach ($tierPriceList as $tierPriceItem) {
                    /** @var AmountInterface $price */
                    $tierPrice = $tierPriceItem['price'];
                    $tierPrices[] = $tierPrice->getValue();
                }
            }
        }

        if (!empty($tierPrices)) {
            return min($tierPrices);
        }

        return 0;
    }
}
