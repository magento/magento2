<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\App\ResourceConnection;

class Full extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * @var \Magento\Framework\Indexer\BatchSizeCalculatorInterface
     */
    protected $batchSizeCalculator;

    /**
     * @var \Magento\Framework\Indexer\BatchProviderInterface
     */
    protected $batchProvider;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var int
     */
    protected $memoryTablesMinRows;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $config
     * @param QueryGenerator|null $queryGenerator
     * @param \Magento\Framework\Indexer\BatchSizeCalculatorInterface|null $batchSizeCalculator
     * @param \Magento\Framework\Indexer\BatchProviderInterface|null $batchProvider
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $config,
        QueryGenerator $queryGenerator = null,
        \Magento\Framework\Indexer\BatchSizeCalculatorInterface $batchSizeCalculator = null,
        \Magento\Framework\Indexer\BatchProviderInterface $batchProvider = null,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->batchSizeCalculator = $batchSizeCalculator ?: $objectManager->get(
            \Magento\Framework\Indexer\BatchSizeCalculatorInterface::class
        );
        $this->batchProvider = $batchProvider ?: $objectManager->get(
            \Magento\Framework\Indexer\BatchProviderInterface::class
        );
        $this->metadataPool = $metadataPool ?: $objectManager->get(
            \Magento\Framework\EntityManager\MetadataPool::class
        );
        $this->memoryTablesMinRows = 200;
        parent::__construct(
            $resource,
            $storeManager,
            $config,
            $queryGenerator
        );
    }

    /**
     * Refresh entities index
     *
     * @return $this
     */
    public function execute()
    {
        $this->clearTmpData();

        $this->reindex();

        $this->publishData();
        $this->removeUnnecessaryData();

        return $this;
    }

    /**
     * Return select for remove unnecessary data
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectUnnecessaryData()
    {
        return $this->connection->select()->from(
            $this->getMainTable(),
            []
        )->joinLeft(
            ['t' => $this->getMainTmpTable()],
            $this->getMainTable() .
            '.category_id = t.category_id AND ' .
            $this->getMainTable() .
            '.store_id = t.store_id AND ' .
            $this->getMainTable() .
            '.product_id = t.product_id',
            []
        )->where(
            't.category_id IS NULL'
        );
    }

    /**
     * Remove unnecessary data
     *
     * @return void
     */
    protected function removeUnnecessaryData()
    {
        $this->connection->query(
            $this->connection->deleteFromSelect($this->getSelectUnnecessaryData(), $this->getMainTable())
        );
    }

    /**
     * Publish data from tmp to index
     *
     * @return void
     */
    protected function publishData()
    {
        $select = $this->connection->select()->from($this->getMainTmpTable());

        $queries = $this->prepareSelectsByRange($select, 'category_id');

        foreach ($queries as $query) {
            $this->connection->query(
                $this->connection->insertFromSelect(
                    $query,
                    $this->getMainTable(),
                    ['category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
        }
    }

    /**
     * Clear all index data
     *
     * @return void
     */
    protected function clearTmpData()
    {
        $this->connection->delete($this->getMainTmpTable());
    }

    /**
     * Reindex all products to root category
     *
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    protected function reindexRootCategory(\Magento\Store\Model\Store $store)
    {
        if ($this->isIndexRootCategoryNeeded()) {
            $this->reindexByCategories($this->getAllProducts($store), 'cp.entity_id IN (?)');
        }
    }

    /**
     * Reindex products of anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    protected function reindexAnchorCategories(\Magento\Store\Model\Store $store)
    {
        $this->reindexByCategories($this->getAnchorCategoriesSelect($store), 'ccp.product_id IN (?)');
    }

    /**
     * Reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    protected function reindexNonAnchorCategories(\Magento\Store\Model\Store $store)
    {
        $this->reindexByCategories($this->getNonAnchorCategoriesSelect($store), 'ccp.product_id IN (?)');
    }

    /**
     * @param \Magento\Framework\DB\Select $resultSelect
     * @param string $whereCondition
     * @return void
     */
    private function reindexByCategories(\Magento\Framework\DB\Select $resultSelect, $whereCondition)
    {
        $entityMetadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $connection = $this->connection;
        $batches = $this->batchProvider->getBatches(
            $connection,
            $entityMetadata->getEntityTable(),
            $entityMetadata->getIdentifierField(),
            $this->batchSizeCalculator->estimateBatchSize($connection, $this->memoryTablesMinRows)
        );
        foreach ($batches as $batch) {
            $select = $connection->select();
            $select->distinct(true);
            $select->from(['e' => $entityMetadata->getEntityTable()], $entityMetadata->getIdentifierField());
            $entityIds = $this->batchProvider->getBatchIds($connection, $select, $batch);
            $resultSelect->where($whereCondition, $entityIds);
            $this->connection->query(
                $this->connection->insertFromSelect(
                    $resultSelect,
                    $this->getMainTmpTable(),
                    ['category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
        }
    }
}
