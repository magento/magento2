<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Calculates prices of custom options of the product with catalog rules applied.
 *
 * @deprecated
 * @see Catalog rule should not apply to custom option
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
     * @return float|null
     */
    public function execute(
        Product $product,
        float $optionPriceValue,
        bool $isPercent
    ): ?float {
        $regularPrice = (float)$product->getPriceInfo()
            ->getPrice(RegularPrice::PRICE_CODE)
            ->getValue();
        $catalogRulePrice = $this->priceModifier->modifyPrice(
            $regularPrice,
            $product
        );
        // Apply catalog price rules to product options only if catalog price rules are applied to product.
        if ($catalogRulePrice < $regularPrice) {
            $optionPrice = $this->getOptionPriceWithoutPriceRule($optionPriceValue, $isPercent, $regularPrice);
            $totalCatalogRulePrice = $this->priceModifier->modifyPrice(
                $regularPrice + $optionPrice,
                $product
            );
            return $totalCatalogRulePrice - $catalogRulePrice;
        }

        return null;
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
