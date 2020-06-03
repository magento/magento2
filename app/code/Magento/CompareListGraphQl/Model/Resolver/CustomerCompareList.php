<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility as CatalogProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory as CompareItemsCollectionFactory;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Store\Api\Data\StoreInterface;

class CustomerCompareList implements ResolverInterface
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
     * @param CompareItemsCollectionFactory $itemCollectionFactory
     * @param CatalogProductVisibility      $catalogProductVisibility
     * @param CatalogConfig                 $catalogConfig
     * @param Compare                       $compareHelper
     * @param PriceProviderPool             $priceProviderPool
     * @param Discount                      $discount
     */
    public function __construct(
        CompareItemsCollectionFactory $itemCollectionFactory,
        CatalogProductVisibility $catalogProductVisibility,
        CatalogConfig $catalogConfig,
        Compare $compareHelper,
        PriceProviderPool $priceProviderPool,
        Discount $discount
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->catalogConfig = $catalogConfig;
        $this->compareProduct = $compareHelper;
        $this->priceProviderPool = $priceProviderPool;
        $this->discount = $discount;
    }

    /**
     * Get customer compare list
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return Value|mixed|void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        return [
            'list_id' => 1,
            'items' => $this->getComparableItems($context, $store),
            'attributes' => $this->getComparableAttributes($context)
        ];
    }

    /**
     * Get comparable items
     *
     * @param ContextInterface $context
     * @param StoreInterface   $store
     *
     * @return array
     */
    private function getComparableItems(ContextInterface $context, StoreInterface $store)
    {
        $items = [];
        foreach ($this->getCollectionComparableItems($context) as $item) {
            /** @var Product $item */
            $items[] = [
                'productId' => $item->getId(),
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'priceRange' => [
                    'minimum_price' => $this->getMinimumProductPrice($item, $store),
                    'maximum_price' => $this->getMinimumProductPrice($item, $store)
                ],
                'canonical_url' => $item->getUrlKey(),
                'images' => [
                    'url' => [
                        'model' => $item,
                        'image_type' => 'image',
                        'label' => $item->getImageLabel()
                    ],
                ],
            ];
        }

        return $items;
    }

    /**
     * Get comparable attributes
     *
     * @param ContextInterface $context
     *
     * @return array
     */
    private function getComparableAttributes(ContextInterface $context): array
    {
        $attributes = [];
        $itemsCollection = $this->getCollectionComparableItems($context);
        foreach ($itemsCollection->getComparableAttributes() as $item) {
            $attributes[] = [
                'code' => $item->getAttributeCode(),
                'title' => $item->getStoreLabel()
            ];
        }

        return $attributes;
    }

    /**
     * Get collection of comparable items
     *
     * @param ContextInterface $context
     *
     * @return Collection
     */
    private function getCollectionComparableItems(ContextInterface $context): Collection
    {
        $this->compareProduct->setAllowUsedFlat(false);
        /** @var Collection $comparableItems */
        $this->items = $this->itemCollectionFactory->create();
        $this->items->setCustomerId($context->getUserId());
        $this->items->useProductItem()->setStoreId($context->getExtensionAttributes()->getStore()->getStoreId());

        $this->items->addAttributeToSelect(
            $this->catalogConfig->getProductAttributes()
        )->loadComparableAttributes()->addMinimalPrice()->addTaxPercents()->setVisibility(
            $this->catalogProductVisibility->getVisibleInSiteIds()
        );

        return $this->items;
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
