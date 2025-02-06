<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Prepares search query based on search text.
 */
class PriceRangeDataProvider
{
    private const STORE_FILTER_CACHE_KEY = '_cache_instance_store_filter';

    private const TYPE_DOWNLOADABLE = 'downloadable';

    /**
     * @param PriceProviderPool $priceProviderPool
     * @param Discount $discount
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        private readonly PriceProviderPool $priceProviderPool,
        private readonly Discount $discount,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {
    }

    /**
     * Prepare Query object based on search text
     *
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array $value
     * @return array
     * @throws LocalizedException
     */
    public function prepare(ContextInterface $context, ResolveInfo $info, array $value): array
    {
        $store = $context->getExtensionAttributes()->getStore();
        $product = $this->getProduct($value, $context, $store);

        $requestedFields = $info->getFieldSelection(10);
        $returnArray = [];

        $returnArray['minimum_price'] = ($requestedFields['minimum_price'] ?? 0) ? ($this->canShowPrice($product) ?
            $this->getMinimumProductPrice($product, $store) : $this->formatEmptyResult()) : $this->formatEmptyResult();
        $returnArray['maximum_price'] = ($requestedFields['maximum_price'] ?? 0) ? ($this->canShowPrice($product) ?
            $this->getMaximumProductPrice($product, $store) : $this->formatEmptyResult()) : $this->formatEmptyResult();

        if ($product->getTypeId() === self::TYPE_DOWNLOADABLE &&
            $product->getData('links_purchased_separately')) {
            $downloadableLinkPrice = (float)$this->getDownloadableLinkPrice($product);
            if ($downloadableLinkPrice > 0) {
                $returnArray['maximum_price']['regular_price']['value'] += $downloadableLinkPrice;
                $returnArray['maximum_price']['final_price']['value'] += $downloadableLinkPrice;
            }
        }

        return $returnArray;
    }

    /**
     * Validate and return product
     *
     * @param array $value
     * @param ContextInterface $context
     * @param StoreInterface $store
     * @return Product
     * @throws LocalizedException
     */
    private function getProduct(array $value, ContextInterface $context, StoreInterface $store): Product
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
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

        return $product;
    }

    /**
     * Get the downloadable link price
     *
     * @param Product $product
     * @return float
     */
    private function getDownloadableLinkPrice(Product $product): float
    {
        $downloadableLinks = $product->getTypeInstance()->getLinks($product);
        if (empty($downloadableLinks)) {
            return 0.0;
        }

        $price = 0.0;
        foreach ($downloadableLinks as $link) {
            $price += (float)$link->getPrice();
        }

        return $price;
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
                'value' => $this->priceCurrency->roundPrice($regularPrice),
                'currency' => $store->getCurrentCurrencyCode(),
            ],
            'final_price' => [
                'value' => $this->priceCurrency->roundPrice($finalPrice),
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
