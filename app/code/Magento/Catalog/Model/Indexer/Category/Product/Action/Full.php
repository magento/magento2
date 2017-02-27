<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\App\ResourceConnection;

/**
 * Class Full reindex action
 *
 * @package Magento\Catalog\Model\Indexer\Category\Product\Action
 */
class Full extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * @var \Magento\Framework\Indexer\BatchSizeCalculatorInterface
     */
    private $batchSizeCalculator;

    /**
     * @var \Magento\Framework\Indexer\BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var int
     */
    private $memoryTablesMinRows;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

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
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null,
        array $memoryTablesMinRows = []
    ) {
        parent::__construct(
            $resource,
            $storeManager,
            $config,
            $queryGenerator
        );
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
        $this->memoryTablesMinRows = $memoryTablesMinRows;
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
        $columns = array_keys($this->connection->describeTable($this->getMainTable()));

        foreach ($queries as $query) {
            $this->connection->query(
                $this->connection->insertFromSelect(
                    $query,
                    $this->getMainTable(),
                    $columns,
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
            $this->reindexCategoriesBySelect($this->getAllProducts($store), 'cp.entity_id IN (?)');
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
        $this->reindexCategoriesBySelect($this->getAnchorCategoriesSelect($store), 'ccp.product_id IN (?)');
    }

    /**
     * Reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    protected function reindexNonAnchorCategories(\Magento\Store\Model\Store $store)
    {
        $this->reindexCategoriesBySelect($this->getNonAnchorCategoriesSelect($store), 'ccp.product_id IN (?)');
    }

    /**
     * Reindex categories using given SQL select and condition.
     *
     * @param \Magento\Framework\DB\Select $resultSelect
     * @param string $whereCondition
     * @return void
     */
    private function reindexCategoriesBySelect(\Magento\Framework\DB\Select $resultSelect, $whereCondition)
    {
        $entityMetadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $columns = array_keys($this->connection->describeTable($this->getMainTmpTable()));
        $batches = $this->batchProvider->getBatches(
            $this->connection,
            $entityMetadata->getEntityTable(),
            $entityMetadata->getIdentifierField(),
            $this->batchSizeCalculator->estimateBatchSize($this->connection, $this->memoryTablesMinRows['default'])
        );
        foreach ($batches as $batch) {
            $select = $this->connection->select();
            $select->distinct(true);
            $select->from(['e' => $entityMetadata->getEntityTable()], $entityMetadata->getIdentifierField());
            $entityIds = $this->batchProvider->getBatchIds($this->connection, $select, $batch);
            $resultSelect->where($whereCondition, $entityIds);
            $this->connection->query(
                $this->connection->insertFromSelect(
                    $resultSelect,
                    $this->getMainTmpTable(),
                    $columns,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
        }
    }
}
