<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Block\Product\Compare\ListCompare;
use Magento\Catalog\Model\CompareList;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\CompareList as ResourceCompareList;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\CompareListGraphQl\Model\Service\Collection\ComparableItems as ComparableItemsCollection;


/**
 * Get comparable items
 */
class ComparableItemsService
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
     * @var ResourceCompareList
     */
    private $resourceCompareList;

    /**
     * @var CompareList
     */
    private $modelCompareList;

    /**
     * @var ListCompare
     */
    private $blockListCompare;

    /**
     * @var ComparableItemsCollection
     */
    private $comparableItemsCollection;

    /**
     * @param PriceProviderPool             $priceProviderPool
     * @param Discount                      $discount
     * @param ResourceCompareList           $resourceCompareList
     * @param CompareList                   $compareList
     * @param ListCompare                   $listCompare
     * @param ComparableItemsCollection     $comparableItemsCollection
     */
    public function __construct(
        PriceProviderPool $priceProviderPool,
        Discount $discount,
        ResourceCompareList $resourceCompareList,
        CompareList $compareList,
        ListCompare $listCompare,
        ComparableItemsCollection $comparableItemsCollection
    ) {
        $this->priceProviderPool = $priceProviderPool;
        $this->discount = $discount;
        $this->resourceCompareList = $resourceCompareList;
        $this->modelCompareList = $compareList;
        $this->blockListCompare = $listCompare;
        $this->comparableItemsCollection = $comparableItemsCollection;
    }

    /**
     * Get comparable items
     *
     * @param int $listId
     * @param ContextInterface $context
     * @param StoreInterface $store
     *
     * @return array
     */
    public function getComparableItems(int $listId, ContextInterface $context, StoreInterface $store)
    {
        $items = [];
        foreach ($this->comparableItemsCollection->getCollectionComparableItems($listId, $context) as $item) {
            /** @var Product $item */
            $items[] = [
                'productId' => $item->getId(),
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'priceRange' => [
                    'minimum_price' => $this->getMinimumProductPrice($item, $store),
                    'maximum_price' => $this->getMaximumProductPrice($item, $store)
                ],
                'canonical_url' => $item->getUrlKey(),
                'images' => [
                    'url' => [
                        'model' => $item,
                        'image_type' => 'image',
                        'label' => $item->getImageLabel()
                    ],
                ],
                'values' => $this->getProductComparableAttributes($listId, $item, $context)
            ];
        }

        return $items;
    }

    /**
     * Get comparable attributes for product
     *
     * @param int $listId
     * @param Product $product
     * @param ContextInterface $context
     *
     * @return array
     */
    private function getProductComparableAttributes(int $listId, Product $product, ContextInterface $context): array
    {
        $attributes = [];
        $itemsCollection = $this->comparableItemsCollection->getCollectionComparableItems($listId, $context);
        foreach ($itemsCollection->getComparableAttributes() as $item) {
            $attributes[] = [
                'code' =>  $item->getAttributeCode(),
                'value' => $this->blockListCompare->getProductAttributeValue($product, $item)
            ];
        }

        return $attributes;
    }

    /**
     * Get formatted minimum product price
     *
     * @param SaleableInterface $product
     * @param StoreInterface $store
     *
     * @return array
     */
    private function getMinimumProductPrice(SaleableInterface $product, StoreInterface $store): array
    {
        $priceProvider = $this->priceProviderPool->getProviderByProductType($product->getTypeId());
        $regularPrice = $priceProvider->getMinimalRegularPrice($product)->getValue();
        $finalPrice = $priceProvider->getMinimalFinalPrice($product)->getValue();
        return $this->formatPrice((float) $regularPrice, (float) $finalPrice, $store);
    }

    /**
     * Get formatted maximum product price
     *
     * @param SaleableInterface $product
     * @param StoreInterface $store
     *
     * @return array
     */
    private function getMaximumProductPrice(SaleableInterface $product, StoreInterface $store): array
    {
        $priceProvider = $this->priceProviderPool->getProviderByProductType($product->getTypeId());
        $regularPrice = $priceProvider->getMaximalRegularPrice($product)->getValue();
        $finalPrice = $priceProvider->getMaximalFinalPrice($product)->getValue();
        return $this->formatPrice((float) $regularPrice, (float) $finalPrice, $store);
    }

    /**
     * Format price for GraphQl output
     *
     * @param float $regularPrice
     * @param float $finalPrice
     * @param StoreInterface $store
     *
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
}
