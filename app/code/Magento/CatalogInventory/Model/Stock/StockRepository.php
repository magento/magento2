<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Stock;

use Magento\CatalogInventory\Api\Data\StockCollectionInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\CatalogInventory\Api\StockRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock as StockResource;
use Magento\CatalogInventory\Model\StockFactory;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\DB\MapperFactory;
use Magento\Framework\DB\QueryBuilderFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class StockRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class StockRepository implements StockRepositoryInterface
{
    /**
     * @var StockResource
     * @since 2.0.0
     */
    protected $resource;

    /**
     * @var StockFactory
     * @since 2.0.0
     */
    protected $stockFactory;

    /**
     * @var StockCollectionInterfaceFactory
     * @since 2.0.0
     */
    protected $stockCollectionFactory;

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
     * @var StockRegistryStorage
     * @since 2.1.0
     */
    protected $stockRegistryStorage;

    /**
     * @param StockResource $resource
     * @param StockFactory $stockFactory
     * @param StockCollectionInterfaceFactory $collectionFactory
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param MapperFactory $mapperFactory
     * @since 2.0.0
     */
    public function __construct(
        StockResource $resource,
        StockFactory $stockFactory,
        StockCollectionInterfaceFactory $collectionFactory,
        QueryBuilderFactory $queryBuilderFactory,
        MapperFactory $mapperFactory
    ) {
        $this->resource = $resource;
        $this->stockFactory = $stockFactory;
        $this->stockCollectionFactory = $collectionFactory;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * @param StockInterface $stock
     * @return StockInterface
     * @throws CouldNotSaveException
     * @since 2.0.0
     */
    public function save(StockInterface $stock)
    {
        try {
            $this->resource->save($stock);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Unable to save Stock'), $exception);
        }
        return $stock;
    }

    /**
     * @param int $stockId
     * @return StockInterface
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function get($stockId)
    {
        $stock = $this->stockFactory->create();
        $this->resource->load($stock, $stockId);
        if (!$stock->getId()) {
            throw new NoSuchEntityException(__('Stock with id "%1" does not exist.', $stockId));
        }
        return $stock;
    }

    /**
     * @param \Magento\CatalogInventory\Api\StockCriteriaInterface $criteria
     * @return \Magento\CatalogInventory\Api\Data\StockCollectionInterface
     * @since 2.0.0
     */
    public function getList(\Magento\CatalogInventory\Api\StockCriteriaInterface $criteria)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->setCriteria($criteria);
        $queryBuilder->setResource($this->resource);
        $query = $queryBuilder->create();
        $collection = $this->stockCollectionFactory->create(['query' => $query]);
        return $collection;
    }

    /**
     * @param StockInterface $stock
     * @return bool|true
     * @throws CouldNotDeleteException
     * @since 2.0.0
     */
    public function delete(StockInterface $stock)
    {
        try {
            $this->resource->delete($stock);
            $this->getStockRegistryStorage()->removeStock();
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Unable to remove Stock with id "%1"', $stock->getStockId()),
                $exception
            );
        }
        return true;
    }

    /**
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     * @since 2.0.0
     */
    public function deleteById($id)
    {
        try {
            $stock = $this->get($id);
            $this->delete($stock);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Unable to remove Stock with id "%1"', $id),
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
