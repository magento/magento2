<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.0.0
 */
class StockRegistryProvider implements StockRegistryProviderInterface
{
    /**
     * @var StockRepositoryInterface
     * @since 2.0.0
     */
    protected $stockRepository;

    /**
     * @var StockInterfaceFactory
     * @since 2.0.0
     */
    protected $stockFactory;

    /**
     * @var StockItemRepositoryInterface
     * @since 2.0.0
     */
    protected $stockItemRepository;

    /**
     * @var StockItemInterfaceFactory
     * @since 2.0.0
     */
    protected $stockItemFactory;

    /**
     * @var StockStatusRepositoryInterface
     * @since 2.0.0
     */
    protected $stockStatusRepository;

    /**
     * @var StockStatusInterfaceFactory
     * @since 2.0.0
     */
    protected $stockStatusFactory;

    /**
     * @var StockCriteriaInterfaceFactory
     * @since 2.0.0
     */
    protected $stockCriteriaFactory;

    /**
     * @var StockItemCriteriaInterfaceFactory
     * @since 2.0.0
     */
    protected $stockItemCriteriaFactory;

    /**
     * @var StockStatusCriteriaInterfaceFactory
     * @since 2.0.0
     */
    protected $stockStatusCriteriaFactory;

    /**
     * @var StockRegistryStorage
     * @since 2.1.0
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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
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
     * @param int|null $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     * @since 2.0.0
     */
    public function getStock($scopeId)
    {
        $stock = $this->getStockRegistryStorage()->getStock($scopeId);
        if (null === $stock) {
            $criteria = $this->stockCriteriaFactory->create();
            $criteria->setScopeFilter($scopeId);
            $collection = $this->stockRepository->getList($criteria);
            $stock = current($collection->getItems());
            if ($stock && $stock->getStockId()) {
                $this->getStockRegistryStorage()->setStock($scopeId, $stock);
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
     * @since 2.0.0
     */
    public function getStockItem($productId, $scopeId)
    {
        $stockItem = $this->getStockRegistryStorage()->getStockItem($productId, $scopeId);
        if (null === $stockItem) {
            $criteria = $this->stockItemCriteriaFactory->create();
            $criteria->setProductsFilter($productId);
            $collection = $this->stockItemRepository->getList($criteria);
            $stockItem = current($collection->getItems());
            if ($stockItem && $stockItem->getItemId()) {
                $this->getStockRegistryStorage()->setStockItem($productId, $scopeId, $stockItem);
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
     * @since 2.0.0
     */
    public function getStockStatus($productId, $scopeId)
    {
        $stockStatus = $this->getStockRegistryStorage()->getStockStatus($productId, $scopeId);
        if (null === $stockStatus) {
            $criteria = $this->stockStatusCriteriaFactory->create();
            $criteria->setProductsFilter($productId);
            $criteria->setScopeFilter($scopeId);
            $collection = $this->stockStatusRepository->getList($criteria);
            $stockStatus = current($collection->getItems());
            if ($stockStatus && $stockStatus->getProductId()) {
                $this->getStockRegistryStorage()->setStockStatus($productId, $scopeId, $stockStatus);
            } else {
                $stockStatus = $this->stockStatusFactory->create();
            }
        }
        return $stockStatus;
    }

    /**
     * @return StockRegistryStorage
     * @since 2.1.0
     */
    private function getStockRegistryStorage()
    {
        if (null === $this->stockRegistryStorage) {
            $this->stockRegistryStorage = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\CatalogInventory\Model\StockRegistryStorage::class);
        }
        return $this->stockRegistryStorage;
    }
}
