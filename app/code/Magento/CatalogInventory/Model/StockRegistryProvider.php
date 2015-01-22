<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Api\StockRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory;
use Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StockRegistryProvider
 */
class StockRegistryProvider implements StockRegistryProviderInterface
{
    /**
     * @var StockRepositoryInterface
     */
    protected $stockRepository;

    /**
     * @var StockInterfaceFactory
     */
    protected $stockFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var StockItemInterfaceFactory
     */
    protected $stockItemFactory;

    /**
     * @var StockStatusRepositoryInterface
     */
    protected $stockStatusRepository;

    /**
     * @var StockStatusInterfaceFactory
     */
    protected $stockStatusFactory;

    /**
     * @var StockCriteriaInterfaceFactory
     */
    protected $stockCriteriaFactory;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    protected $stockItemCriteriaFactory;

    /**
     * @var StockStatusCriteriaInterfaceFactory
     */
    protected $stockStatusCriteriaFactory;

    /**
     * @var array
     */
    protected $stocks = [];

    /**
     * @var array
     */
    protected $stockItems = [];

    /**
     * @var array
     */
    protected $stockStatuses = [];

    /**
     * @param StockRepositoryInterface $stockRepository
     * @param StockInterfaceFactory $stockFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemInterfaceFactory $stockItemFactory
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param StockStatusInterfaceFactory $stockStatusFactory
     * @param StockCriteriaInterfaceFactory $stockCriteriaFactory
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
     */
    public function __construct(
        StockRepositoryInterface $stockRepository,
        StockInterfaceFactory $stockFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockItemInterfaceFactory $stockItemFactory,
        StockStatusRepositoryInterface $stockStatusRepository,
        StockStatusInterfaceFactory $stockStatusFactory,
        StockCriteriaInterfaceFactory $stockCriteriaFactory,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
    ) {
        $this->stockRepository = $stockRepository;
        $this->stockFactory = $stockFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemFactory = $stockItemFactory;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->stockStatusFactory = $stockStatusFactory;

        $this->stockCriteriaFactory = $stockCriteriaFactory;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockStatusCriteriaFactory = $stockStatusCriteriaFactory;
    }

    /**
     * @param int|null $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($websiteId)
    {
        if (!isset($this->stocks[$websiteId])) {
            $criteria = $this->stockCriteriaFactory->create();
            $criteria->setWebsiteFilter($websiteId);
            $collection = $this->stockRepository->getList($criteria);
            $stock = current($collection->getItems());
            if ($stock && $stock->getStockId()) {
                $this->stocks[$websiteId] = $stock;
            } else {
                return $this->stockFactory->create();
            }
        }
        return $this->stocks[$websiteId];
    }

    /**
     * @param int $productId
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $websiteId)
    {
        $key = $websiteId . '/' . $productId;
        if (!isset($this->stockItems[$key])) {
            $criteria = $this->stockItemCriteriaFactory->create();
            $criteria->setProductsFilter($productId);
            $criteria->setWebsiteFilter($websiteId);
            $collection = $this->stockItemRepository->getList($criteria);
            $stockItem = current($collection->getItems());
            if ($stockItem && $stockItem->getItemId()) {
                $this->stockItems[$key] = $stockItem;
            } else {
                return $this->stockItemFactory->create();
            }
        }
        return $this->stockItems[$key];
    }

    /**
     * @param int $productId
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $websiteId)
    {
        $key = $websiteId . '/' . $productId;
        if (!isset($this->stockStatuses[$key])) {
            $criteria = $this->stockStatusCriteriaFactory->create();
            $criteria->setProductsFilter($productId);
            $criteria->setWebsiteFilter($websiteId);
            $collection = $this->stockStatusRepository->getList($criteria);
            $stockStatus = current($collection->getItems());
            if ($stockStatus && $stockStatus->getProductId()) {
                $this->stockStatuses[$key] = $stockStatus;
            } else {
                return $this->stockStatusFactory->create();
            }
        }
        return $this->stockStatuses[$key];
    }
}
