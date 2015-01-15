<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;

/**
 * Class StockRegistry
 */
class StockRegistry implements StockRegistryInterface
{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory
     */
    protected $criteriaFactory;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemCriteriaInterfaceFactory $criteriaFactory
     * @param ProductFactory $productFactory
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockItemRepositoryInterface $stockItemRepository,
        StockItemCriteriaInterfaceFactory $criteriaFactory,
        ProductFactory $productFactory
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockItemRepository = $stockItemRepository;
        $this->criteriaFactory = $criteriaFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        return $this->stockRegistryProvider->getStock($websiteId);
    }

    /**
     * @param int $productId
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        return $this->stockRegistryProvider->getStockItem($productId, $websiteId);
    }

    /**
     * @param string $productSku
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItemBySku($productSku, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        $productId = $this->resolveProductId($productSku);
        return $this->stockRegistryProvider->getStockItem($productId, $websiteId);
    }

    /**
     * @param int $productId
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        return $this->stockRegistryProvider->getStockStatus($productId, $websiteId);
    }

    /**
     * @param string $productSku
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockStatusBySku($productSku, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        $productId = $this->resolveProductId($productSku);
        return $this->getStockStatus($productId, $websiteId);
    }

    /**
     * Retrieve Product stock status
     * @param int $productId
     * @param int $websiteId
     * @return int
     */
    public function getProductStockStatus($productId, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        $stockStatus = $this->getStockStatus($productId, $websiteId);
        return $stockStatus->getStockStatus();
    }

    /**
     * @param string $productSku
     * @param null $websiteId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductStockStatusBySku($productSku, $websiteId = null)
    {
        //if (!$websiteId) {
        $websiteId = $this->stockConfiguration->getDefaultWebsiteId();
        //}
        $productId = $this->resolveProductId($productSku);
        return $this->getProductStockStatus($productId, $websiteId);
    }

    /**
     * @inheritdoc
     */
    public function getLowStockItems($websiteId, $qty, $currentPage = 1, $pageSize = 0)
    {
        $criteria = $this->criteriaFactory->create();
        $criteria->setLimit($currentPage, $pageSize);
        $criteria->setWebsiteFilter($websiteId);
        $criteria->setQtyFilter('>=', $qty);
        $criteria->addField('qty');
        return $this->stockItemRepository->getList($criteria);
    }

    /**
     * @inheritdoc
     */
    public function updateStockItemBySku($productSku, \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem)
    {
        $productId = $this->resolveProductId($productSku);
        $websiteId = $stockItem->getWebsiteId() ?: null;
        $origStockItem = $this->getStockItem($productId, $websiteId);
        $data = $stockItem->getData();
        if ($origStockItem->getItemId()) {
            if (isset($data['item_id'])) {
                unset($data['item_id']);
            }
        }
        $origStockItem->addData($data);
        $origStockItem->setProductId($productId);
        return $this->stockItemRepository->save($origStockItem)->getItemId();
    }

    /**
     * @param string $productSku
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function resolveProductId($productSku)
    {
        $product = $this->productFactory->create();
        $productId = $product->getIdBySku($productSku);
        if (!$productId) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                "Product with SKU \"{$productSku}\" does not exist"
            );
        }
        return $productId;
    }
}
