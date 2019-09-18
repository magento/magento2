<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Prices;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Format product's pricing information for price_range field
 */
class PriceRange implements ResolverInterface
{
    /**
     * @var Prices
     */
    private $productPrices;

    /**
     * @var Discount
     */
    private $discount;

    /**
     * @param Prices $productPrices
     * @param Discount $discount
     */
    public function __construct(Prices $productPrices, Discount $discount)
    {
        $this->productPrices = $productPrices;
        $this->discount = $discount;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        /** @var Product $product */
        $product = $value['model'];
        $product->unsetData('minimal_price');

        return [
            'minimum_price' => $this->getMinimumProductPrice($product, $store),
            'maximum_price' => $this->getMaximumProductPrice($product, $store)
        ];
    }

    /**
     * Get formatted minimum product price
     *
     * @param SaleableInterface $product
     * @param StoreInterface $store
     * @return array
     */
    private function getMinimumProductPrice(SaleableInterface $product, StoreInterface $store): array
    {
        $regularPrice = $this->productPrices->getMinimalRegularPrice($product)->getValue();
        $finalPrice = $this->productPrices->getMinimalFinalPrice($product)->getValue();

        return $this->formatPrice($regularPrice, $finalPrice, $store);
    }

    /**
     * Get formatted maximum product price
     *
     * @param SaleableInterface $product
     * @param StoreInterface $store
     * @return array
     */
    private function getMaximumProductPrice(SaleableInterface $product, StoreInterface $store): array
    {
        $regularPrice = $this->productPrices->getMaximalRegularPrice($product)->getValue();
        $finalPrice = $this->productPrices->getMaximalFinalPrice($product)->getValue();

        return $this->formatPrice($regularPrice, $finalPrice, $store);
    }

    /**
     * Format price for GraphQl output
     *
     * @param float $regularPrice
     * @param float $finalPrice
     * @param StoreInterface $store
     * @return array
     */
    private function formatPrice(float $regularPrice, float $finalPrice, StoreInterface $store): array
    {
        return [
            'regular_price' => [
                'value' => $regularPrice,
                'currency' => $store->getCurrentCurrencyCode()
            ],
            'final_price' => [
                'value' => $finalPrice,
                'currency' => $store->getCurrentCurrencyCode()
            ],
            'discount' => $this->discount->getPriceDiscount($regularPrice, $finalPrice),
        ];
    }


}