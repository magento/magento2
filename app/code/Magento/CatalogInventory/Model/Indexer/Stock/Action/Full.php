<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\StockFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\BatchSizeManagementInterface;
use Magento\Framework\Indexer\BatchProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogInventory\Model\Indexer\Stock\AbstractAction;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\StockInterface;

/**
 * Class Full reindex action
 *
 * @package Magento\CatalogInventory\Model\Indexer\Stock\Action
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Full extends AbstractAction
{
    /**
     * Action type representation
     */
    const ACTION_TYPE = 'full';

    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @var BatchSizeManagementInterface
     * @since 2.2.0
     */
    private $batchSizeManagement;

    /**
     * @var BatchProviderInterface
     * @since 2.2.0
     */
    private $batchProvider;

    /**
     * @var array
     * @since 2.2.0
     */
    private $batchRowsCount;

    /**
     * @var ActiveTableSwitcher
     * @since 2.2.0
     */
    private $activeTableSwitcher;

    /**
     * @param ResourceConnection $resource
     * @param StockFactory $indexerFactory
     * @param ProductType $catalogProductType
     * @param CacheContext $cacheContext
     * @param EventManager $eventManager
     * @param MetadataPool|null $metadataPool
     * @param BatchSizeManagementInterface|null $batchSizeManagement
     * @param BatchProviderInterface|null $batchProvider
     * @param array $batchRowsCount
     * @param ActiveTableSwitcher|null $activeTableSwitcher
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.2.0
     */
    public function __construct(
        ResourceConnection $resource,
        StockFactory $indexerFactory,
        ProductType $catalogProductType,
        CacheContext $cacheContext,
        EventManager $eventManager,
        MetadataPool $metadataPool = null,
        BatchSizeManagementInterface $batchSizeManagement = null,
        BatchProviderInterface $batchProvider = null,
        array $batchRowsCount = [],
        ActiveTableSwitcher $activeTableSwitcher = null
    ) {
        parent::__construct(
            $resource,
            $indexerFactory,
            $catalogProductType,
            $cacheContext,
            $eventManager
        );

        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
        $this->batchProvider = $batchProvider ?: ObjectManager::getInstance()->get(BatchProviderInterface::class);
        $this->batchSizeManagement = $batchSizeManagement ?: ObjectManager::getInstance()->get(
            \Magento\CatalogInventory\Model\Indexer\Stock\BatchSizeManagement::class
        );
        $this->batchRowsCount = $batchRowsCount;
        $this->activeTableSwitcher = $activeTableSwitcher ?: ObjectManager::getInstance()
            ->get(ActiveTableSwitcher::class);
    }

    /**
     * Execute Full reindex
     *
     * @param null|array $ids
     * @throws LocalizedException
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute($ids = null)
    {
        try {
            $this->useIdxTable(false);
            $this->cleanIndexersTables($this->_getTypeIndexers());

            $entityMetadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);

            $columns = array_keys($this->_getConnection()->describeTable($this->_getIdxTable()));

            /** @var \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock $indexer */
            foreach ($this->_getTypeIndexers() as $indexer) {
                $indexer->setActionType(self::ACTION_TYPE);
                $connection = $indexer->getConnection();
                $tableName = $this->activeTableSwitcher->getAdditionalTableName($indexer->getMainTable());

                $batchRowCount = isset($this->batchRowsCount[$indexer->getTypeId()])
                    ? $this->batchRowsCount[$indexer->getTypeId()]
                    : $this->batchRowsCount['default'];

                $this->batchSizeManagement->ensureBatchSize($connection, $batchRowCount);
                $batches = $this->batchProvider->getBatches(
                    $connection,
                    $entityMetadata->getEntityTable(),
                    $entityMetadata->getIdentifierField(),
                    $batchRowCount
                );

                foreach ($batches as $batch) {
                    $this->clearTemporaryIndexTable();
                    // Get entity ids from batch
                    $select = $connection->select();
                    $select->distinct(true);
                    $select->from(['e' => $entityMetadata->getEntityTable()], $entityMetadata->getIdentifierField());
                    $select->where('type_id = ?', $indexer->getTypeId());

                    $entityIds = $this->batchProvider->getBatchIds($connection, $select, $batch);
                    if (!empty($entityIds)) {
                        $indexer->reindexEntity($entityIds);
                        $select = $connection->select()->from($this->_getIdxTable(), $columns);
                        $query = $select->insertFromSelect($tableName, $columns);
                        $connection->query($query);
                    }
                }
            }
            $this->activeTableSwitcher->switchTable($indexer->getConnection(), [$indexer->getMainTable()]);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Delete all records from index table
     * Used to clean table before re-indexation
     *
     * @param array $indexers
     * @return void
     * @since 2.2.0
     */
    private function cleanIndexersTables(array $indexers)
    {
        $tables = array_map(
            function (StockInterface $indexer) {
                return $this->activeTableSwitcher->getAdditionalTableName($indexer->getMainTable());
            },
            $indexers
        );

        $tables = array_unique($tables);

        foreach ($tables as $table) {
            $this->_getConnection()->truncateTable($table);
        }
    }
}
