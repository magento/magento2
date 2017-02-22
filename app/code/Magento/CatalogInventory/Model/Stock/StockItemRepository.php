<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Stock;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemCollectionInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface as StockItemRepositoryInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as StockItemResource;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\Framework\DB\MapperFactory;
use Magento\Framework\DB\QueryBuilderFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class StockItemRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockItemRepository implements StockItemRepositoryInterface
{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var StockStateProviderInterface
     */
    protected $stockStateProvider;

    /**
     * @var StockItemResource
     */
    protected $resource;

    /**
     * @var StockItemInterfaceFactory
     */
    protected $stockItemFactory;

    /**
     * @var StockItemCollectionInterfaceFactory
     */
    protected $stockItemCollectionFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var QueryBuilderFactory
     */
    protected $queryBuilderFactory;

    /**
     * @var MapperFactory
     */
    protected $mapperFactory;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var Processor
     */
    protected $indexProcessor;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockStateProviderInterface $stockStateProvider
     * @param StockItemResource $resource
     * @param StockItemInterfaceFactory $stockItemFactory
     * @param StockItemCollectionInterfaceFactory $stockItemCollectionFactory
     * @param ProductFactory $productFactory
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param MapperFactory $mapperFactory
     * @param TimezoneInterface $localeDate
     * @param Processor $indexProcessor
     * @param DateTime $dateTime
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockStateProviderInterface $stockStateProvider,
        StockItemResource $resource,
        StockItemInterfaceFactory $stockItemFactory,
        StockItemCollectionInterfaceFactory $stockItemCollectionFactory,
        ProductFactory $productFactory,
        QueryBuilderFactory $queryBuilderFactory,
        MapperFactory $mapperFactory,
        TimezoneInterface $localeDate,
        Processor $indexProcessor,
        DateTime $dateTime
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockStateProvider = $stockStateProvider;
        $this->resource = $resource;
        $this->stockItemFactory = $stockItemFactory;
        $this->stockItemCollectionFactory = $stockItemCollectionFactory;
        $this->productFactory = $productFactory;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->mapperFactory = $mapperFactory;
        $this->mapperFactory = $mapperFactory;
        $this->localeDate = $localeDate;
        $this->indexProcessor = $indexProcessor;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem)
    {
        try {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productFactory->create();
            $product->load($stockItem->getProductId());
            if (!$product->getId()) {
                return $stockItem;
            }
            $typeId = $product->getTypeId() ?: $product->getTypeInstance()->getTypeId();
            $isQty = $this->stockConfiguration->isQty($typeId);
            if ($isQty) {
                $isInStock = $this->stockStateProvider->verifyStock($stockItem);
                if ($stockItem->getManageStock() && !$isInStock) {
                    $stockItem->setIsInStock(false)->setStockStatusChangedAutomaticallyFlag(true);
                }
                // if qty is below notify qty, update the low stock date to today date otherwise set null
                $stockItem->setLowStockDate(null);
                if ($this->stockStateProvider->verifyNotification($stockItem)) {
                    $stockItem->setLowStockDate($this->dateTime->gmtDate());
                }
                $stockItem->setStockStatusChangedAuto(0);
                if ($stockItem->hasStockStatusChangedAutomaticallyFlag()) {
                    $stockItem->setStockStatusChangedAuto((int)$stockItem->getStockStatusChangedAutomaticallyFlag());
                }
            } else {
                $stockItem->setQty(0);
            }

            $stockItem->setWebsiteId($stockItem->getWebsiteId());
            $stockItem->setStockId($stockItem->getStockId());

            $this->resource->save($stockItem);

            $this->indexProcessor->reindexRow($stockItem->getProductId());
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $stockItem;
    }

    /**
     * @inheritdoc
     */
    public function get($stockItemId)
    {
        $stockItem = $this->stockItemFactory->create();
        $this->resource->load($stockItem, $stockItemId);
        if (!$stockItem->getItemId()) {
            throw new NoSuchEntityException(__('Stock Item with id "%1" does not exist.', $stockItemId));
        }
        return $stockItem;
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->setCriteria($criteria);
        $queryBuilder->setResource($this->resource);
        $query = $queryBuilder->create();
        $collection = $this->stockItemCollectionFactory->create(['query' => $query]);
        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function delete(StockItemInterface $stockItem)
    {
        try {
            $this->resource->delete($stockItem);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        try {
            $stockItem = $this->get($id);
            $this->delete($stockItem);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }
}
