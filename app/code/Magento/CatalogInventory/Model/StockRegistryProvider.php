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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var StockRegistryStorage
     */
    protected $stockRegistryStorage;

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
     * @param StockRegistryStorage $stockRegistryStorage
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        StockRegistryStorage $stockRegistryStorage
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
        $this->stockRegistryStorage = $stockRegistryStorage;
    }

    /**
     * @param int|null $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($scopeId)
    {
        $stock = $this->stockRegistryStorage->getStock($scopeId);
        if (null === $stock) {
            $criteria = $this->stockCriteriaFactory->create();
            $criteria->setScopeFilter($scopeId);
            $collection = $this->stockRepository->getList($criteria);
            $stock = current($collection->getItems());
            if ($stock && $stock->getStockId()) {
                $this->stockRegistryStorage->setStock($scopeId, $stock);
            } else {
                $stock = $this->stockFactory->create();
            }
        }
        return $stock;
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $scopeId)
    {
        $stockItem = $this->stockRegistryStorage->getStockItem($productId, $scopeId);
        if (null === $stockItem) {
            $criteria = $this->stockItemCriteriaFactory->create();
            $criteria->setProductsFilter($productId);
            $collection = $this->stockItemRepository->getList($criteria);
            $stockItem = current($collection->getItems());
            if ($stockItem && $stockItem->getItemId()) {
                $this->stockRegistryStorage->setStockItem($productId, $scopeId, $stockItem);
            } else {
                $stockItem = $this->stockItemFactory->create();
            }
        }
        return $stockItem;
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $scopeId)
    {
        $stockStatus = $this->stockRegistryStorage->getStockStatus($productId, $scopeId);
        if (null === $stockStatus) {
            $criteria = $this->stockStatusCriteriaFactory->create();
            $criteria->setProductsFilter($productId);
            $criteria->setScopeFilter($scopeId);
            $collection = $this->stockStatusRepository->getList($criteria);
            $stockStatus = current($collection->getItems());
            if ($stockStatus && $stockStatus->getProductId()) {
                $this->stockRegistryStorage->setStockStatus($productId, $scopeId, $stockStatus);
            } else {
                $stockStatus = $this->stockStatusFactory->create();
            }
        }
        return $stockStatus;
    }
}
