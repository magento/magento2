<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Calculates prices of custom options of the product with catalog rules applied.
 */
class CalculateCustomOptionCatalogRule
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var PriceModifierInterface
     */
    private $priceModifier;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param PriceModifierInterface $priceModifier
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        PriceModifierInterface $priceModifier
    ) {
        $this->priceModifier = $priceModifier;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Calculate prices of custom options of the product with catalog rules applied.
     *
     * @param Product $product
     * @param float $optionPriceValue
     * @param bool $isPercent
     * @return float
     */
    public function execute(
        Product $product,
        float $optionPriceValue,
        bool $isPercent
    ): float {
        $regularPrice = (float)$product->getPriceInfo()
            ->getPrice(RegularPrice::PRICE_CODE)
            ->getValue();
        $catalogRulePrice = $this->priceModifier->modifyPrice(
            $regularPrice,
            $product
        );
        $basePriceWithOutCatalogRules = (float)$this->getGetBasePriceWithOutCatalogRules($product);
        // Apply catalog price rules to product options only if catalog price rules are applied to product.
        if ($catalogRulePrice < $basePriceWithOutCatalogRules) {
            $optionPrice = $this->getOptionPriceWithoutPriceRule($optionPriceValue, $isPercent, $regularPrice);
            $totalCatalogRulePrice = $this->priceModifier->modifyPrice(
                $regularPrice + $optionPrice,
                $product
            );
            $finalOptionPrice = $totalCatalogRulePrice - $catalogRulePrice;
        } else {
            $finalOptionPrice = $this->getOptionPriceWithoutPriceRule(
                $optionPriceValue,
                $isPercent,
                $this->getGetBasePriceWithOutCatalogRules($product)
            );
        }

        return $this->priceCurrency->convertAndRound($finalOptionPrice);
    }

    /**
     * Get product base price without catalog rules applied.
     *
     * @param Product $product
     * @return float
     */
    private function getGetBasePriceWithOutCatalogRules(Product $product): float
    {
        $basePrice = null;
        foreach ($product->getPriceInfo()->getPrices() as $price) {
            if ($price instanceof BasePriceProviderInterface
                && $price->getPriceCode() !== CatalogRulePrice::PRICE_CODE
                && $price->getValue() !== false
            ) {
                $basePrice = min(
                    $price->getValue(),
                    $basePrice ?? $price->getValue()
                );
            }
        }

        return $basePrice ?? $product->getPrice();
    }

    /**
     * Calculate option price without catalog price rule discount.
     *
     * @param float $optionPriceValue
     * @param bool $isPercent
     * @param float $basePrice
     * @return float
     */
    private function getOptionPriceWithoutPriceRule(float $optionPriceValue, bool $isPercent, float $basePrice): float
    {
        return $isPercent ? $basePrice * $optionPriceValue / 100 : $optionPriceValue;
    }
}
