<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Product\Price;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * Provides product prices for configurable products
 */
class Provider implements ProviderInterface
{
    /**
     * @var ConfigurableOptionsProviderInterface
     */
    private $optionsProvider;

    /**
     * @var array
     */
    private $minimalPrice = [
        FinalPrice::PRICE_CODE => [],
        RegularPrice::PRICE_CODE => []
    ];

    /**
     * @var array
     */
    private $maximalPrice = [
        FinalPrice::PRICE_CODE => [],
        RegularPrice::PRICE_CODE => []
    ];

    /**
     * @param ConfigurableOptionsProviderInterface $optionsProvider
     */
    public function __construct(
        ConfigurableOptionsProviderInterface $optionsProvider
    ) {
        $this->optionsProvider = $optionsProvider;
    }

    /**
     * @inheritdoc
     */
    public function getMinimalFinalPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getMinimalPrice($product, FinalPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getMinimalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getMinimalPrice($product, RegularPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getMaximalFinalPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getMaximalPrice($product, FinalPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getMaximalRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $this->getMaximalPrice($product, RegularPrice::PRICE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getRegularPrice(SaleableInterface $product): AmountInterface
    {
        return $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
    }

    /**
     * Get minimal price from child products
     *
     * @param SaleableInterface $product
     * @param string $code
     * @return AmountInterface
     */
    private function getMinimalPrice(SaleableInterface $product, string $code): AmountInterface
    {
        if (!isset($this->minimalPrice[$code][$product->getId()])) {
            $minimumAmount = null;
            foreach ($this->filterDisabledProducts($this->optionsProvider->getProducts($product)) as $variant) {
                $variantAmount = $variant->getPriceInfo()->getPrice($code)->getAmount();
                if (!$minimumAmount || ($variantAmount->getValue() < $minimumAmount->getValue())) {
                    $minimumAmount = $variantAmount;
                    $this->minimalPrice[$code][$product->getId()] = $variantAmount;
                }
            }
        }

        return $this->minimalPrice[$code][$product->getId()];
    }

    /**
     * Get maximal price from child products
     *
     * @param SaleableInterface $product
     * @param string $code
     * @return AmountInterface
     */
    private function getMaximalPrice(SaleableInterface $product, string $code): AmountInterface
    {
        if (!isset($this->maximalPrice[$code][$product->getId()])) {
            $maximumAmount = null;
            foreach ($this->optionsProvider->getProducts($product) as $variant) {
                $variantAmount = $variant->getPriceInfo()->getPrice($code)->getAmount();
                if (!$maximumAmount || ($variantAmount->getValue() > $maximumAmount->getValue())) {
                    $maximumAmount = $variantAmount;
                    $this->maximalPrice[$code][$product->getId()] = $variantAmount;
                }
            }
        }

        return $this->maximalPrice[$code][$product->getId()];
    }

    /**
     * Filter out disabled products
     *
     * @param array $products
     * @return array
     */
    private function filterDisabledProducts(array $products): array
    {
        return array_filter($products, function ($product) {
            return (int)$product->getStatus() === ProductStatus::STATUS_ENABLED;
        });
    }
}
