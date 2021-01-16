<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\CatalogInventory\Model\Indexer\Stock\BatchSizeManagement;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\StockFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\DB\Query\BatchIteratorInterface;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Full extends AbstractAction
{
    /**
     * Action type representation
     */
    const ACTION_TYPE = 'full';

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var BatchSizeManagementInterface
     */
    private $batchSizeManagement;

    /**
     * @var BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var array
     */
    private $batchRowsCount;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var QueryGenerator|null
     */
    private $batchQueryGenerator;

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
     * @param QueryGenerator|null $batchQueryGenerator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        ActiveTableSwitcher $activeTableSwitcher = null,
        QueryGenerator $batchQueryGenerator = null
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
            BatchSizeManagement::class
        );
        $this->batchRowsCount = $batchRowsCount;
        $this->activeTableSwitcher = $activeTableSwitcher ?: ObjectManager::getInstance()
            ->get(ActiveTableSwitcher::class);
        $this->batchQueryGenerator = $batchQueryGenerator ?: ObjectManager::getInstance()->get(QueryGenerator::class);
    }

    /**
     * Execute Full reindex
     *
     * @param null|array $ids
     * @throws LocalizedException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids = null): void
    {
        try {
            $this->useIdxTable(false);
            $this->cleanIndexersTables($this->_getTypeIndexers());

            $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);

            $columns = array_keys($this->_getConnection()->describeTable($this->_getIdxTable()));

            /** @var DefaultStock $indexer */
            foreach ($this->_getTypeIndexers() as $indexer) {
                $indexer->setActionType(self::ACTION_TYPE);
                $connection = $indexer->getConnection();
                $tableName = $this->activeTableSwitcher->getAdditionalTableName($indexer->getMainTable());

                $batchRowCount = isset($this->batchRowsCount[$indexer->getTypeId()])
                    ? $this->batchRowsCount[$indexer->getTypeId()]
                    : $this->batchRowsCount['default'];

                $this->batchSizeManagement->ensureBatchSize($connection, $batchRowCount);

                $select = $connection->select();
                $select->distinct(true);
                $select->from(
                    [
                        'e' => $entityMetadata->getEntityTable()
                    ],
                    $entityMetadata->getIdentifierField()
                )->where(
                    'type_id = ?',
                    $indexer->getTypeId()
                );

                $batchQueries = $this->batchQueryGenerator->generate(
                    $entityMetadata->getIdentifierField(),
                    $select,
                    $batchRowCount,
                    BatchIteratorInterface::UNIQUE_FIELD_ITERATOR
                );

                foreach ($batchQueries as $query) {
                    $this->clearTemporaryIndexTable();
                    $entityIds = $connection->fetchCol($query);
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
     *
     * Used to clean table before re-indexation
     *
     * @param array $indexers
     * @return void
     */
    private function cleanIndexersTables(array $indexers): void
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
