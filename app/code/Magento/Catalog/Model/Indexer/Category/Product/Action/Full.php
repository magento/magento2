<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\BatchProviderInterface;
use Magento\Framework\Indexer\BatchSizeManagementInterface;
use Magento\Indexer\Model\ProcessManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Full reindex action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Full extends AbstractAction
{
    /**
     * @var BatchSizeManagementInterface
     */
    private $batchSizeManagement;

    /**
     * @var BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var MetadataPool
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
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param QueryGenerator|null $queryGenerator
     * @param BatchSizeManagementInterface|null $batchSizeManagement
     * @param BatchProviderInterface|null $batchProvider
     * @param MetadataPool|null $metadataPool
     * @param int|null $batchRowsCount
     * @param ActiveTableSwitcher|null $activeTableSwitcher
     * @param ProcessManager $processManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        Config $config,
        QueryGenerator $queryGenerator = null,
        BatchSizeManagementInterface $batchSizeManagement = null,
        BatchProviderInterface $batchProvider = null,
        MetadataPool $metadataPool = null,
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
        $objectManager = ObjectManager::getInstance();
        $this->batchSizeManagement = $batchSizeManagement ?: $objectManager->get(
            BatchSizeManagementInterface::class
        );
        $this->batchProvider = $batchProvider ?: $objectManager->get(
            BatchProviderInterface::class
        );
        $this->metadataPool = $metadataPool ?: $objectManager->get(
            MetadataPool::class
        );
        $this->batchRowsCount = $batchRowsCount;
        $this->activeTableSwitcher = $activeTableSwitcher ?: $objectManager->get(ActiveTableSwitcher::class);
        $this->processManager = $processManager ?: $objectManager->get(ProcessManager::class);
    }

    /**
     * Create the store tables
     *
     * @return void
     */
    private function createTables(): void
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->tableMaintainer->createTablesForStore((int)$store->getId());
        }
    }

    /**
     * Truncates the replica tables
     *
     * @return void
     */
    private function clearReplicaTables(): void
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->connection->truncateTable($this->tableMaintainer->getMainReplicaTable((int)$store->getId()));
        }
    }

    /**
     * Switches the active table
     *
     * @return void
     */
    private function switchTables(): void
    {
        $tablesToSwitch = [];
        foreach ($this->storeManager->getStores() as $store) {
            $tablesToSwitch[] = $this->tableMaintainer->getMainTable((int)$store->getId());
        }
        $this->activeTableSwitcher->switchTable($this->connection, $tablesToSwitch);
    }

    /**
     * Refresh entities index
     *
     * @return $this
     */
    public function execute(): Full
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
    protected function reindex(): void
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
     * @param Store $store
     */
    private function reindexStore($store): void
    {
        $this->reindexRootCategory($store);
        $this->reindexAnchorCategories($store);
        $this->reindexNonAnchorCategories($store);
    }

    /**
     * Publish data from tmp to replica table
     *
     * @param Store $store
     * @return void
     */
    private function publishData($store): void
    {
        $select = $this->connection->select()->from($this->tableMaintainer->getMainTmpTable((int)$store->getId()));
        $columns = array_keys(
            $this->connection->describeTable($this->tableMaintainer->getMainReplicaTable((int)$store->getId()))
        );
        $tableName = $this->tableMaintainer->getMainReplicaTable((int)$store->getId());

        $this->connection->query(
            $this->connection->insertFromSelect(
                $select,
                $tableName,
                $columns,
                AdapterInterface::INSERT_ON_DUPLICATE
            )
        );
    }

    /**
     * @inheritdoc
     */
    protected function reindexRootCategory(Store $store): void
    {
        if ($this->isIndexRootCategoryNeeded()) {
            $this->reindexCategoriesBySelect($this->getAllProducts($store), 'cp.entity_id IN (?)', $store);
        }
    }

    /**
     * Reindex products of anchor categories
     *
     * @param Store $store
     * @return void
     */
    protected function reindexAnchorCategories(Store $store): void
    {
        $this->reindexCategoriesBySelect($this->getAnchorCategoriesSelect($store), 'ccp.product_id IN (?)', $store);
    }

    /**
     * Reindex products of non anchor categories
     *
     * @param Store $store
     * @return void
     */
    protected function reindexNonAnchorCategories(Store $store): void
    {
        $this->reindexCategoriesBySelect($this->getNonAnchorCategoriesSelect($store), 'ccp.product_id IN (?)', $store);
    }

    /**
     * Reindex categories using given SQL select and condition.
     *
     * @param Select $basicSelect
     * @param string $whereCondition
     * @param Store $store
     * @return void
     */
    private function reindexCategoriesBySelect(Select $basicSelect, $whereCondition, $store): void
    {
        $this->tableMaintainer->createMainTmpTable((int)$store->getId());

        $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $columns = array_keys(
            $this->connection->describeTable($this->tableMaintainer->getMainTmpTable((int)$store->getId()))
        );
        $this->batchSizeManagement->ensureBatchSize($this->connection, $this->batchRowsCount);

        $select = $this->connection->select();
        $select->distinct(true);
        $select->from(['e' => $entityMetadata->getEntityTable()], $entityMetadata->getIdentifierField());

        $batchQueries = $this->prepareSelectsByRange(
            $select,
            $entityMetadata->getIdentifierField(),
            (int)$this->batchRowsCount
        );

        foreach ($batchQueries as $query) {
            $this->connection->delete($this->tableMaintainer->getMainTmpTable((int)$store->getId()));
            $entityIds = $this->connection->fetchCol($query);
            $resultSelect = clone $basicSelect;
            $resultSelect->where($whereCondition, $entityIds);
            $this->connection->query(
                $this->connection->insertFromSelect(
                    $resultSelect,
                    $this->tableMaintainer->getMainTmpTable((int)$store->getId()),
                    $columns,
                    AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
            $this->publishData($store);
        }
    }
}
