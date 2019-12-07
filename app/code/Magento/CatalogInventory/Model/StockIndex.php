<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Website as ProductWebsite;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockIndexInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Class StockIndex
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockIndex implements StockIndexInterface
{
    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    protected $stockStatusResource;

    /**
     * @var ProductType
     */
    protected $productType;

    /**
     * Retrieve website models
     *
     * @var array
     */
    protected $websites;

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    protected $productTypes = [];

    /**
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param ProductRepositoryInterface $productRepository
     * @param ProductWebsite $productWebsite
     * @param ProductType $productType
     */
    public function __construct(
        StockRegistryProviderInterface $stockRegistryProvider,
        ProductRepositoryInterface $productRepository,
        ProductWebsite $productWebsite,
        ProductType $productType
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->productRepository = $productRepository;
        $this->productWebsite = $productWebsite;
        $this->productType = $productType;
    }

    /**
     * Rebuild stock index of the given website
     *
     * @param int $productId
     * @param int $scopeId
     * @deprecated 100.1.0
     * @return true
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function rebuild($productId = null, $scopeId = null)
    {
        if ($productId !== null) {
            $this->updateProductStockStatus($productId, $scopeId);
        } else {
            $lastProductId = 0;
            while (true) {
                /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $resource */
                $resource = $this->getStockStatusResource();
                $productCollection = $resource->getProductCollection($lastProductId);
                if (!$productCollection) {
                    break;
                }
                foreach ($productCollection as $productId => $productType) {
                    $lastProductId = $productId;
                    $this->updateProductStockStatus($productId, $scopeId);
                }
            }
        }
        return true;
    }

    /**
     * Update product status from stock item
     *
     * @param int $productId
     * @param int $websiteId
     * @deprecated 100.1.0
     * @return void
     */
    public function updateProductStockStatus($productId, $websiteId)
    {
        $item = $this->stockRegistryProvider->getStockItem($productId, $websiteId);

        $status = \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK;
        $qty = 0;
        if ($item->getItemId()) {
            $status = $item->getIsInStock();
            $qty = $item->getQty();
        }
        $this->processChildren($productId, $item->getWebsiteId(), $qty, $status);
        $this->processParents($productId, $item->getWebsiteId());
    }

    /**
     * Process children stock status
     *
     * @param int $productId
     * @param int $websiteId
     * @param int $qty
     * @param int $status
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function processChildren(
        $productId,
        $websiteId,
        $qty = 0,
        $status = \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK
    ) {
        if ($status == \Magento\CatalogInventory\Model\Stock\Status::STATUS_OUT_OF_STOCK) {
            $this->getStockStatusResource()->saveProductStatus($productId, $status, $qty, $websiteId);
            return;
        }

        $statuses = [];
        $websitesWithStores = $this->getWebsitesWithDefaultStores($websiteId);

        foreach (array_keys($websitesWithStores) as $websiteId) {
            /* @var $website \Magento\Store\Model\Website */
            $statuses[$websiteId] = $status;
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId);
        $typeInstance = $product->getTypeInstance();

        $requiredChildrenIds = $typeInstance->getChildrenIds($productId, true);
        if ($requiredChildrenIds) {
            $childrenIds = [];
            foreach ($requiredChildrenIds as $groupedChildrenIds) {
                $childrenIds = array_merge($childrenIds, $groupedChildrenIds);
            }
            $childrenWebsites = $this->productWebsite->getWebsites($childrenIds);
            foreach ($websitesWithStores as $websiteId => $storeId) {
                $childrenStatus = $this->getStockStatusResource()->getProductStatus($childrenIds, $storeId);
                $childrenStock = $this->getStockStatusResource()->getProductsStockStatuses($childrenIds, $websiteId);
                $websiteStatus = $statuses[$websiteId];
                foreach ($requiredChildrenIds as $groupedChildrenIds) {
                    $optionStatus = false;
                    foreach ($groupedChildrenIds as $childId) {
                        if (isset($childrenStatus[$childId])
                            && isset($childrenWebsites[$childId])
                            && in_array($websiteId, $childrenWebsites[$childId])
                            && $childrenStatus[$childId] == Status::STATUS_ENABLED
                            && isset($childrenStock[$childId])
                            && $childrenStock[$childId] == \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK
                        ) {
                            $optionStatus = true;
                        }
                    }
                    $websiteStatus = $websiteStatus && $optionStatus;
                }
                $statuses[$websiteId] = (int)$websiteStatus;
            }
        }
        foreach ($statuses as $websiteId => $websiteStatus) {
            $this->getStockStatusResource()->saveProductStatus($productId, $websiteStatus, $qty, $websiteId);
        }
    }

    /**
     * Retrieve website models
     *
     * @param int|null $websiteId
     * @return array
     */
    protected function getWebsitesWithDefaultStores($websiteId = null)
    {
        if ($this->websites === null) {
            /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $resource */
            $resource = $this->getStockStatusResource();
            $this->websites = $resource->getWebsiteStores();
        }
        $websites = $this->websites;
        if ($websiteId !== null && isset($this->websites[$websiteId])) {
            $websites = [$websiteId => $this->websites[$websiteId]];
        }
        return $websites;
    }

    /**
     * Process Parents by child
     *
     * @param int $productId
     * @param int $websiteId
     * @return $this
     */
    protected function processParents($productId, $websiteId)
    {
        $parentIds = [];
        foreach ($this->getProductTypeInstances() as $typeInstance) {
            /* @var $typeInstance AbstractType */
            $parentIds = array_merge($parentIds, $typeInstance->getParentIdsByChild($productId));
        }

        if (!$parentIds) {
            return $this;
        }

        foreach ($parentIds as $parentId) {
            $item = $this->stockRegistryProvider->getStockItem($parentId, $websiteId);
            $status = \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK;
            $qty = 0;
            if ($item->getItemId()) {
                $status = $item->getIsInStock();
                $qty = $item->getQty();
            }
            $this->processChildren($parentId, $websiteId, $qty, $status);
        }
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     */
    protected function getProductTypeInstances()
    {
        if (empty($this->productTypes)) {
            $productEmulator = new \Magento\Framework\DataObject();
            foreach (array_keys($this->productType->getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->productTypes[$typeId] = $this->productType->factory($productEmulator);
            }
        }
        return $this->productTypes;
    }

    /**
     * @return \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    protected function getStockStatusResource()
    {
        if (empty($this->stockStatusResource)) {
            $this->stockStatusResource = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\CatalogInventory\Model\ResourceModel\Stock\Status::class
            );
        }
        return $this->stockStatusResource;
    }
}
