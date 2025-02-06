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
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
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
use Psr\Log\LoggerInterface as PsrLogger;

/**
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
     * @deprecated 100.2.0
     * @see No longer used
     */
    protected $indexProcessor;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var StockRegistryStorage
     */
    protected $stockRegistryStorage;

    /**
     * @var  \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var PsrLogger
     */
    private $psrLogger;

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
     * @param CollectionFactory|null $productCollectionFactory
     * @param PsrLogger|null $psrLogger
     * @param StockRegistryStorage|null $stockRegistryStorage
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
        DateTime $dateTime,
        ?\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory = null,
        ?PsrLogger $psrLogger = null,
        ?StockRegistryStorage $stockRegistryStorage = null
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
        $this->psrLogger = $psrLogger ?: ObjectManager::getInstance()
            ->get(PsrLogger::class);
        $this->stockRegistryStorage = $stockRegistryStorage
            ?? ObjectManager::getInstance()->get(StockRegistryStorage::class);
    }

    /**
     * @inheritdoc
     */
    public function save(StockItemInterface $stockItem)
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
                $this->updateStockStatus($stockItem);
                // if qty is below notify qty, update the low stock date to today date otherwise set null
                $stockItem->setLowStockDate(null);
                if ($this->stockStateProvider->verifyNotification($stockItem)) {
                    $stockItem->setLowStockDate($this->dateTime->gmtDate());
                }

                if ($stockItem->hasStockStatusChangedAutomaticallyFlag()) {
                    $stockItem->setStockStatusChangedAuto((int)$stockItem->getStockStatusChangedAutomaticallyFlag());
                }
            } else {
                $stockItem->setQty(0);
            }

            $stockItem->setWebsiteId($stockItem->getWebsiteId());
            $stockItem->setStockId($stockItem->getStockId());

            $this->resource->save($stockItem);
        } catch (\Exception $exception) {
            $this->psrLogger->error($exception->getMessage());
            throw new CouldNotSaveException(__('The stock item was unable to be saved. Please try again.'), $exception);
        }
        return $stockItem;
    }

    /**
     * Update stock status based on stock configuration
     *
     * @param StockItemInterface $stockItem
     * @return void
     */
    private function updateStockStatus(StockItemInterface $stockItem): void
    {
        $isInStock = $this->stockStateProvider->verifyStock($stockItem);
        if ($stockItem->getManageStock()) {
            if (!$isInStock) {
                if ($stockItem->getIsInStock() === true) {
                    $stockItem->setIsInStock(false);
                    $stockItem->setStockStatusChangedAuto(1);
                }
            } else {
                if ($this->hasStockStatusChanged($stockItem)) {
                    $stockItem->setStockStatusChangedAuto(0);
                }
                if ($stockItem->getIsInStock() === false && $stockItem->getStockStatusChangedAuto()) {
                    $stockItem->setIsInStock(true);
                }
            }
        } else {
            $stockItem->setStockStatusChangedAuto(0);
        }
    }

    /**
     * Check if stock status has changed
     *
     * @param StockItemInterface $stockItem
     * @return bool
     */
    private function hasStockStatusChanged(StockItemInterface $stockItem): bool
    {
        if ($stockItem->getItemId()) {
            try {
                $existingStockItem = $this->get($stockItem->getItemId());
                return $existingStockItem->getIsInStock() !== $stockItem->getIsInStock();
            } catch (NoSuchEntityException $e) {
                return true;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function get($stockItemId)
    {
        $stockItem = $this->stockItemFactory->create();
        $this->resource->load($stockItem, $stockItemId);
        if (!$stockItem->getItemId()) {
            throw new NoSuchEntityException(
                __('The stock item with the "%1" ID wasn\'t found. Verify the ID and try again.', $stockItemId)
            );
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
            $this->stockRegistryStorage->removeStockItem($stockItem->getProductId());
            $this->stockRegistryStorage->removeStockStatus($stockItem->getProductId());
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'The stock item with the "%1" ID wasn\'t found. Verify the ID and try again.',
                    $stockItem->getItemId()
                ),
                $exception
            );
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
            throw new CouldNotDeleteException(
                __('The stock item with the "%1" ID wasn\'t found. Verify the ID and try again.', $id),
                $exception
            );
        }
        return true;
    }
}
