<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
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
     * @var Discount
     */
    private $discount;

    /**
     * @var PriceProviderPool
     */
    private $priceProviderPool;

    /**
     * @param PriceProviderPool $priceProviderPool
     * @param Discount $discount
     */
    public function __construct(PriceProviderPool $priceProviderPool, Discount $discount)
    {
        $this->priceProviderPool = $priceProviderPool;
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

        $requestedFields = $info->getFieldSelection(10);
        $returnArray = [];

        if (isset($requestedFields['minimum_price'])) {
            $returnArray['minimum_price'] =  $this->canShowPrice($product) ?
                $this->getMinimumProductPrice($product, $store) : $this->formatEmptyResult();
        }
        if (isset($requestedFields['maximum_price'])) {
            $returnArray['maximum_price'] =  $this->canShowPrice($product) ?
                $this->getMaximumProductPrice($product, $store) : $this->formatEmptyResult();
        }
        return $returnArray;
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
        $priceProvider = $this->priceProviderPool->getProviderByProductType($product->getTypeId());
        $regularPrice = $priceProvider->getMinimalRegularPrice($product)->getValue();
        $finalPrice = $priceProvider->getMinimalFinalPrice($product)->getValue();
        $minPriceArray = $this->formatPrice((float) $regularPrice, (float) $finalPrice, $store);
        $minPriceArray['model'] = $product;
        return $minPriceArray;
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
        $priceProvider = $this->priceProviderPool->getProviderByProductType($product->getTypeId());
        $regularPrice = $priceProvider->getMaximalRegularPrice($product)->getValue();
        $finalPrice = $priceProvider->getMaximalFinalPrice($product)->getValue();
        $maxPriceArray = $this->formatPrice((float) $regularPrice, (float) $finalPrice, $store);
        $maxPriceArray['model'] = $product;
        return $maxPriceArray;
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
            'discount' => $this->discount->getDiscountByDifference($regularPrice, $finalPrice),
        ];
    }

    /**
     * Check if the product is allowed to show price
     *
     * @param ProductInterface $product
     * @return bool
     */
    private function canShowPrice($product): bool
    {
        if ($product->hasData('can_show_price') && $product->getData('can_show_price') === false) {
            return false;
        }

        return true;
    }

    /**
     * Format empty result
     *
     * @return array
     */
    private function formatEmptyResult(): array
    {
        return [
            'regular_price' => [
                'value' => null,
                'currency' => null
            ],
            'final_price' => [
                'value' => null,
                'currency' => null
            ],
            'discount' => null
        ];
    }
}
