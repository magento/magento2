<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProductGraphQl\Model\Resolver\Product\Price;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderInterface;

/**
 * Provides product prices for configurable products
 */
class Provider implements ProviderInterface
{
    /**
     * PriceInfo cache
     *
     * @var array
     */
    private $productPrices = [];

    /**
     * @var PriceInfoFactory
     */
    private $priceInfoFactory;

    /**
     * Cache product prices so only fetch once
     *
     * @var AmountInterface[]
     */
    private $minimalProductAmounts;

    /**
     * @param PriceInfoFactory $priceInfoFactory
     */
    public function __construct(
        PriceInfoFactory $priceInfoFactory
    ) {
        $this->priceInfoFactory = $priceInfoFactory;
    }

    /**
     * @inheritdoc
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getMinimalProductAmount($product, FinalPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getMinimalProductAmount($product, RegularPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface
    {
        //Use minimal for maximal since maximal price in infinite
        return $this->getMinimalProductAmount($product, FinalPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface
    {
        //Use minimal for maximal since maximal price in infinite
        return $this->getMinimalProductAmount($product, RegularPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getRegularPrice(SaleableInterface $product): AmountInterface
    {
        $priceInfo = $this->getProductPriceInfo($product);
        return $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getAmount();
    }

    /**
     * Get minimal amount for cheapest product in group
     *
     * @param SaleableInterface $product
     * @param string $priceType
     * @return AmountInterface
     */
    private function getMinimalProductAmount(SaleableInterface $product, string $priceType): AmountInterface
    {
        if (empty($this->minimalProductAmounts[$product->getId()][$priceType])) {
            $products = $product->getTypeInstance()->getAssociatedProducts($product);
            $minPrice = null;
            foreach ($products as $item) {
                $price = $this->getProductPriceInfo($item)->getPrice($priceType);
                $priceValue = $price->getValue();
                if (($priceValue !== false) && ($priceValue <= ($minPrice === null ? $priceValue : $minPrice))) {
                    $minPrice = $price->getValue();
                    $this->minimalProductAmounts[$product->getId()][$priceType] = $price->getAmount();
                }
            }
        }

        return $this->minimalProductAmounts[$product->getId()][$priceType];
    }

    /**
     * Get price info for product
     *
     * @param SaleableInterface $product
     * @return PriceInfoInterface
     */
    private function getProductPriceInfo($product)
    {
        if (!isset($this->productPrices[$product->getId()])) {
            $this->productPrices[$product->getId()] = $this->priceInfoFactory->create($product);
        }

        return $this->productPrices[$product->getId()];
    }
}
