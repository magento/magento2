<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Bundle\Model\ResourceModel\Selection as BundleSelection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class to return ids of options and child products when all products in required option are disabled in bundle product
 */
class SelectionProductsDisabledRequired
{
    /**
     * @var BundleSelection
     */
    private $bundleSelection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Status
     */
    private $catalogProductStatus;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var string
     */
    private $hasStockStatusFilter = 'has_stock_status_filter';

    /**
     * @var array
     */
    private $productsDisabledRequired = [];

    /**
     * @param BundleSelection $bundleSelection
     * @param StoreManagerInterface $storeManager
     * @param Status $catalogProductStatus
     * @param ProductCollectionFactory $productCollectionFactory
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        BundleSelection $bundleSelection,
        StoreManagerInterface $storeManager,
        Status $catalogProductStatus,
        ProductCollectionFactory $productCollectionFactory,
        MetadataPool $metadataPool
    ) {
        $this->bundleSelection = $bundleSelection;
        $this->storeManager = $storeManager;
        $this->catalogProductStatus = $catalogProductStatus;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Return ids of options and child products when all products in required option are disabled in bundle product
     *
     * @param int $bundleId
     * @param int|null $websiteId
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getChildProductIds(int $bundleId, ?int $websiteId = null): array
    {
        if (!$websiteId) {
            $websiteId = (int)$this->storeManager->getStore()->getWebsiteId();
        }
        $cacheKey = $this->getCacheKey($bundleId, $websiteId);
        if (isset($this->productsDisabledRequired[$cacheKey])) {
            return $this->productsDisabledRequired[$cacheKey];
        }
        $selectionProductIds = $this->bundleSelection->getChildrenIds($bundleId);
        /** for cases when no required products found */
        if (count($selectionProductIds) === 1 && isset($selectionProductIds[0])) {
            $this->productsDisabledRequired[$cacheKey] = [];
            return $this->productsDisabledRequired[$cacheKey];
        }
        $products = $this->getProducts($selectionProductIds, $websiteId);
        if (!$products) {
            $this->productsDisabledRequired[$cacheKey] = [];
            return $this->productsDisabledRequired[$cacheKey];
        }
        foreach ($selectionProductIds as $optionId => $optionProductIds) {
            foreach ($optionProductIds as $productId) {
                if (isset($products[$productId])) {
                    /** @var Product $product */
                    $product = $products[$productId];
                    if (in_array($product->getStatus(), $this->catalogProductStatus->getVisibleStatusIds())) {
                        unset($selectionProductIds[$optionId]);
                    }
                }
            }
        }
        $this->productsDisabledRequired[$cacheKey] = $selectionProductIds;
        return $this->productsDisabledRequired[$cacheKey];
    }

    /**
     * Get products objects
     *
     * @param array $selectionProductIds
     * @param int $websiteId
     * @return ProductInterface[]
     */
    private function getProducts(array $selectionProductIds, int $websiteId): array
    {
        $productIds = [];
        $defaultStore = $this->storeManager->getWebsite($websiteId)->getDefaultStore();
        $defaultStoreId = $defaultStore ? $defaultStore->getId() : null;
        foreach ($selectionProductIds as $optionProductIds) {
            $productIds[] = $optionProductIds;
        }
        $productIds = array_merge([], ...$productIds);
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->joinAttribute(
            ProductInterface::STATUS,
            Product::ENTITY . '/' . ProductInterface::STATUS,
            $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField(),
            null,
            'inner',
            $defaultStoreId
        );
        $productCollection->addIdFilter($productIds);
        $productCollection->addStoreFilter($defaultStoreId);
        $productCollection->setFlag($this->hasStockStatusFilter, true);
        return $productCollection->getItems();
    }

    /**
     * Get cache key
     *
     * @param int $bundleId
     * @param int $websiteId
     * @return string
     */
    private function getCacheKey(int $bundleId, int $websiteId): string
    {
        return $bundleId . '-' . $websiteId;
    }
}
