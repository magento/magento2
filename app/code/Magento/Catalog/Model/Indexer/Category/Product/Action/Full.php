<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\App\ResourceConnection;
use Magento\Indexer\Model\ProcessManager;

/**
 * Class Full reindex action
 *
 * @package Magento\Catalog\Model\Indexer\Category\Product\Action
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Full extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * @var \Magento\Framework\Indexer\BatchSizeManagementInterface
     */
    private $batchSizeManagement;

    /**
     * @var \Magento\Framework\Indexer\BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * Row count to process in a batch
     *
     * @var int
     */
    private $batchRowsCount;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var ProcessManager
     */
    private $processManager;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $config
     * @param QueryGenerator|null $queryGenerator
     * @param \Magento\Framework\Indexer\BatchSizeManagementInterface|null $batchSizeManagement
     * @param \Magento\Framework\Indexer\BatchProviderInterface|null $batchProvider
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     * @param int|null $batchRowsCount
     * @param ActiveTableSwitcher|null $activeTableSwitcher
     * @param ProcessManager $processManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $config,
        QueryGenerator $queryGenerator = null,
        \Magento\Framework\Indexer\BatchSizeManagementInterface $batchSizeManagement = null,
        \Magento\Framework\Indexer\BatchProviderInterface $batchProvider = null,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null,
        $batchRowsCount = null,
        ActiveTableSwitcher $activeTableSwitcher = null,
        ProcessManager $processManager = null
    ) {
        parent::__construct(
            $resource,
            $storeManager,
            $config,
            $queryGenerator
        );
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->batchSizeManagement = $batchSizeManagement ?: $objectManager->get(
            \Magento\Framework\Indexer\BatchSizeManagementInterface::class
        );
        $this->batchProvider = $batchProvider ?: $objectManager->get(
            \Magento\Framework\Indexer\BatchProviderInterface::class
        );
        $this->metadataPool = $metadataPool ?: $objectManager->get(
            \Magento\Framework\EntityManager\MetadataPool::class
        );
        $this->batchRowsCount = $batchRowsCount;
        $this->activeTableSwitcher = $activeTableSwitcher ?: $objectManager->get(ActiveTableSwitcher::class);
        $this->processManager = $processManager ?: $objectManager->get(ProcessManager::class);
    }

    /**
     * @return void
     */
    private function createTables()
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->tableMaintainer->createTablesForStore($store->getId());
        }
    }

    /**
     * @return void
     */
    private function clearReplicaTables()
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->connection->truncateTable($this->tableMaintainer->getMainReplicaTable($store->getId()));
        }
    }

    /**
     * @return void
     */
    private function switchTables()
    {
        $tablesToSwitch = [];
        foreach ($this->storeManager->getStores() as $store) {
            $tablesToSwitch[] = $this->tableMaintainer->getMainTable($store->getId());
        }
        $this->activeTableSwitcher->switchTable($this->connection, $tablesToSwitch);
    }

    /**
     * Refresh entities index
     *
     * @return $this
     */
    public function execute()
    {
        $this->createTables();
        $this->clearReplicaTables();
        $this->reindex();
        $this->switchTables();
        return $this;
    }

    /**
     * Run reindexation
     *
     * @return void
     */
    protected function reindex()
    {
        $userFunctions = [];

        foreach ($this->storeManager->getStores() as $store) {
            if ($this->getPathFromCategoryId($store->getRootCategoryId())) {
                $userFunctions[$store->getId()] = function () use ($store) {
                    return $this->reindexStore($store);
                };
            }
        }

        $this->processManager->execute($userFunctions);
    }

    /**
     * Execute indexation by store
     *
     * @param \Magento\Store\Model\Store $store
     */
    private function reindexStore($store)
    {
        $this->reindexRootCategory($store);
        $this->reindexAnchorCategories($store);
        $this->reindexNonAnchorCategories($store);
    }

    /**
     * Publish data from tmp to replica table
     *
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    private function publishData($store)
    {
        $select = $this->connection->select()->from($this->tableMaintainer->getMainTmpTable($store->getId()));
        $columns = array_keys(
            $this->connection->describeTable($this->tableMaintainer->getMainReplicaTable($store->getId()))
        );
        $tableName = $this->tableMaintainer->getMainReplicaTable($store->getId());

        $this->connection->query(
            $this->connection->insertFromSelect(
                $select,
                $tableName,
                $columns,
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function reindexRootCategory(\Magento\Store\Model\Store $store)
    {
        if ($this->isIndexRootCategoryNeeded()) {
            $this->reindexCategoriesBySelect($this->getAllProducts($store), 'cp.entity_id IN (?)', $store);
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
        $this->reindexCategoriesBySelect($this->getAnchorCategoriesSelect($store), 'ccp.product_id IN (?)', $store);
    }

    /**
     * Reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    protected function reindexNonAnchorCategories(\Magento\Store\Model\Store $store)
    {
        $this->reindexCategoriesBySelect($this->getNonAnchorCategoriesSelect($store), 'ccp.product_id IN (?)', $store);
    }

    /**
     * Reindex categories using given SQL select and condition.
     *
     * @param \Magento\Framework\DB\Select $basicSelect
     * @param string $whereCondition
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    private function reindexCategoriesBySelect(\Magento\Framework\DB\Select $basicSelect, $whereCondition, $store)
    {
        $this->tableMaintainer->createMainTmpTable($store->getId());

        $entityMetadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $columns = array_keys(
            $this->connection->describeTable($this->tableMaintainer->getMainTmpTable($store->getId()))
        );
        $this->batchSizeManagement->ensureBatchSize($this->connection, $this->batchRowsCount);
        $batches = $this->batchProvider->getBatches(
            $this->connection,
            $entityMetadata->getEntityTable(),
            $entityMetadata->getIdentifierField(),
            $this->batchRowsCount
        );
        foreach ($batches as $batch) {
            $this->connection->delete($this->tableMaintainer->getMainTmpTable($store->getId()));
            $resultSelect = clone $basicSelect;
            $select = $this->connection->select();
            $select->distinct(true);
            $select->from(['e' => $entityMetadata->getEntityTable()], $entityMetadata->getIdentifierField());
            $entityIds = $this->batchProvider->getBatchIds($this->connection, $select, $batch);
            $resultSelect->where($whereCondition, $entityIds);
            $this->connection->query(
                $this->connection->insertFromSelect(
                    $resultSelect,
                    $this->tableMaintainer->getMainTmpTable($store->getId()),
                    $columns,
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
            $this->publishData($store);
        }
    }
}
