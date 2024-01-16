<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Prepares search query based on search text.
 */
class PriceRangeDataProvider
{
    private const STORE_FILTER_CACHE_KEY = '_cache_instance_store_filter';

    /**
     * @var Discount
     */
    private Discount $discount;

    /**
     * @var PriceProviderPool
     */
    private PriceProviderPool $priceProviderPool;

    /**
     * @param PriceProviderPool $priceProviderPool
     * @param Discount $discount
     */
    public function __construct(
        PriceProviderPool $priceProviderPool,
        Discount $discount
    ) {
        $this->priceProviderPool = $priceProviderPool;
        $this->discount = $discount;
    }

    /**
     * Prepare Query object based on search text
     *
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array $value
     * @throws Exception
     * @return mixed|Value
     */
    public function prepare(ContextInterface $context, ResolveInfo $info, array $value): array
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        /** @var Product $product */
        $product = $value['model'];
        $product->unsetData('minimal_price');
        // add store filter for the product
        $product->setData(self::STORE_FILTER_CACHE_KEY, $store);

        if ($context) {
            $customerGroupId = $context->getExtensionAttributes()->getCustomerGroupId();
            if ($customerGroupId !== null) {
                $product->setCustomerGroupId($customerGroupId);
            }
        }

        $requestedFields = $info->getFieldSelection(10);
        $returnArray = [];

        $returnArray['minimum_price'] = ($requestedFields['minimum_price'] ?? 0) ? ($this->canShowPrice($product) ?
            $this->getMinimumProductPrice($product, $store) : $this->formatEmptyResult()) : $this->formatEmptyResult();
        $returnArray['maximum_price'] = ($requestedFields['maximum_price'] ?? 0) ? ($this->canShowPrice($product) ?
            $this->getMaximumProductPrice($product, $store) : $this->formatEmptyResult()) : $this->formatEmptyResult();

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
        $minPriceArray = $this->formatPrice(
            (float)$priceProvider->getMinimalRegularPrice($product)->getValue(),
            (float)$priceProvider->getMinimalFinalPrice($product)->getValue(),
            $store
        );
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
        $maxPriceArray = $this->formatPrice(
            (float)$priceProvider->getMaximalRegularPrice($product)->getValue(),
            (float)$priceProvider->getMaximalFinalPrice($product)->getValue(),
            $store
        );
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
                'currency' => $store->getCurrentCurrencyCode(),
            ],
            'final_price' => [
                'value' => $finalPrice,
                'currency' => $store->getCurrentCurrencyCode(),
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
    private function canShowPrice(ProductInterface $product): bool
    {
        return $product->hasData('can_show_price') ? $product->getData('can_show_price') : true;
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
                'currency' => null,
            ],
            'final_price' => [
                'value' => null,
                'currency' => null,
            ],
            'discount' => null,
        ];
    }
}
