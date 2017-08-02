<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\MapperFactory;
use Magento\Framework\DB\QueryBuilderFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class StockItemRepository implements StockItemRepositoryInterface
{
    /**
     * @var StockConfigurationInterface
     * @since 2.0.0
     */
    protected $stockConfiguration;

    /**
     * @var StockStateProviderInterface
     * @since 2.0.0
     */
    protected $stockStateProvider;

    /**
     * @var StockItemResource
     * @since 2.0.0
     */
    protected $resource;

    /**
     * @var StockItemInterfaceFactory
     * @since 2.0.0
     */
    protected $stockItemFactory;

    /**
     * @var StockItemCollectionInterfaceFactory
     * @since 2.0.0
     */
    protected $stockItemCollectionFactory;

    /**
     * @var ProductFactory
     * @since 2.0.0
     */
    protected $productFactory;

    /**
     * @var QueryBuilderFactory
     * @since 2.0.0
     */
    protected $queryBuilderFactory;

    /**
     * @var MapperFactory
     * @since 2.0.0
     */
    protected $mapperFactory;

    /**
     * @var TimezoneInterface
     * @since 2.0.0
     */
    protected $localeDate;

    /**
     * @var Processor
     * @since 2.0.0
     */
    protected $indexProcessor;

    /**
     * @var DateTime
     * @since 2.0.0
     */
    protected $dateTime;

    /**
     * @var StockRegistryStorage
     * @since 2.1.0
     */
    protected $stockRegistryStorage;

    /**
     * @var  \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     * @since 2.2.0
     */
    protected $productCollectionFactory;

    /**
     * Constructor
     *
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
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|null $collectionFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
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
        DateTime $dateTime,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory = null
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockStateProvider = $stockStateProvider;
        $this->resource = $resource;
        $this->stockItemFactory = $stockItemFactory;
        $this->stockItemCollectionFactory = $stockItemCollectionFactory;
        $this->productFactory = $productFactory;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->mapperFactory = $mapperFactory;
        $this->localeDate = $localeDate;
        $this->indexProcessor = $indexProcessor;
        $this->dateTime = $dateTime;
        $this->productCollectionFactory = $productCollectionFactory ?: ObjectManager::getInstance()
            ->get(CollectionFactory::class);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function save(\Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem)
    {
        try {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productCollectionFactory->create()
                ->setFlag('has_stock_status_filter')
                ->addIdFilter($stockItem->getProductId())
                ->addFieldToSelect('type_id')
                ->getFirstItem();

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
            throw new CouldNotSaveException(__('Unable to save Stock Item'), $exception);
        }
        return $stockItem;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function delete(StockItemInterface $stockItem)
    {
        try {
            $this->resource->delete($stockItem);
            $this->getStockRegistryStorage()->removeStockItem($stockItem->getProductId());
            $this->getStockRegistryStorage()->removeStockStatus($stockItem->getProductId());
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Unable to remove Stock Item with id "%1"', $stockItem->getItemId()),
                $exception
            );
        }
        return true;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function deleteById($id)
    {
        try {
            $stockItem = $this->get($id);
            $this->delete($stockItem);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Unable to remove Stock Item with id "%1"', $id),
                $exception
            );
        }
        return true;
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
