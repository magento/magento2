<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Service;

use Magento\Catalog\Block\Product\Compare\ListCompare;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\CompareList;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility as CatalogProductVisibility;
use Magento\Catalog\Model\ResourceModel\CompareList as ResourceCompareList;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory as CompareItemsCollectionFactory;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Store\Api\Data\StoreInterface;


class CompareListService
{
    /**
     * @var Collection
     */
    private $items;

    /**
     * @var CompareItemsCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var CatalogProductVisibility
     */
    private $catalogProductVisibility;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @var Compare
     */
    private $compareProduct;

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
     * @param CompareItemsCollectionFactory $itemCollectionFactory
     * @param CatalogProductVisibility      $catalogProductVisibility
     * @param CatalogConfig                 $catalogConfig
     * @param Compare                       $compareHelper
     * @param PriceProviderPool             $priceProviderPool
     * @param Discount                      $discount
     * @param ResourceCompareList           $resourceCompareList
     * @param CompareList                   $compareList
     * @param ListCompare                   $listCompare
     */
    public function __construct(
        CompareItemsCollectionFactory $itemCollectionFactory,
        CatalogProductVisibility $catalogProductVisibility,
        CatalogConfig $catalogConfig,
        Compare $compareHelper,
        PriceProviderPool $priceProviderPool,
        Discount $discount,
        ResourceCompareList $resourceCompareList,
        CompareList $compareList,
        ListCompare $listCompare
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->catalogConfig = $catalogConfig;
        $this->compareProduct = $compareHelper;
        $this->priceProviderPool = $priceProviderPool;
        $this->discount = $discount;
        $this->resourceCompareList = $resourceCompareList;
        $this->modelCompareList = $compareList;
        $this->blockListCompare = $listCompare;
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
        foreach ($this->getCollectionComparableItems($listId, $context) as $item) {
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
     * Get comparable attributes
     *
     * @param int $listId
     * @param ContextInterface $context
     *
     * @return array
     */
    public function getComparableAttributes(int $listId, ContextInterface $context): array
    {
        $attributes = [];
        $itemsCollection = $this->getCollectionComparableItems($listId, $context);
        foreach ($itemsCollection->getComparableAttributes() as $item) {
            $attributes[] = [
                'code' => $item->getAttributeCode(),
                'title' => $item->getStoreLabel()
            ];
        }

        return $attributes;
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
        $itemsCollection = $this->getCollectionComparableItems($listId, $context);
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

    /**
     * Get collection of comparable items
     *
     * @param int $listId
     * @param ContextInterface $context
     *
     * @return Collection
     */
    private function getCollectionComparableItems(int $listId, ContextInterface $context): Collection
    {
        $this->compareProduct->setAllowUsedFlat(false);
        /** @var Collection $comparableItems */
        $this->items = $this->itemCollectionFactory->create();
        $this->items->setListId($listId);
        $this->items->useProductItem()->setStoreId($context->getExtensionAttributes()->getStore()->getStoreId());
        $this->items->addAttributeToSelect(
            $this->catalogConfig->getProductAttributes()
        )->loadComparableAttributes()->addMinimalPrice()->addTaxPercents()->setVisibility(
            $this->catalogProductVisibility->getVisibleInSiteIds()
        );

        return $this->items;
    }
}
