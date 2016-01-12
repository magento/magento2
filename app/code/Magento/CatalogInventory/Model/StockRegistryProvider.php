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
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StockRegistryProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

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
     * @param StockConfigurationInterface $stockConfiguration
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
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory,
        StockConfigurationInterface $stockConfiguration
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
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @inheritdoc
     */
    public function getStock($stockId)
    {
        if (!isset($this->stocks[$stockId])) {
            if ($stockId !== null) {
                $stock = $this->stockRepository->get($stockId);
            } else {
                /** @var \Magento\CatalogInventory\Api\StockCriteriaInterface $criteria */
                $criteria = $this->stockCriteriaFactory->create();
                $criteria->setScopeFilter($this->stockConfiguration->getDefaultScopeId());
                $collection = $this->stockRepository->getList($criteria);
                $stock = current($collection->getItems());
            }
            if ($stock && $stock->getStockId()) {
                $this->stocks[$stockId] = $stock;
            } else {
                return $this->stockFactory->create();
            }
        }
        return $this->stocks[$stockId];
    }

    /**
     * @param int $productId
     * @param int $stockId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $stockId)
    {
        $key = $stockId . '/' . $productId;
        if (!isset($this->stockItems[$key])) {
            /** @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria */
            $criteria = $this->stockItemCriteriaFactory->create();
            $criteria->setProductsFilter($productId);
            $criteria->setStockFilter($this->getStock($stockId));
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
     * @param int $stockId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $stockId)
    {
        $key = $stockId . '/' . $productId;
        if (!isset($this->stockStatuses[$key])) {
            /** @var \Magento\CatalogInventory\Api\stockStatusCriteriaInterface $criteria */
            $criteria = $this->stockStatusCriteriaFactory->create();
            $criteria->setProductsFilter($productId);
            $criteria->addFilter('stock', 'stock_id', $stockId);
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
