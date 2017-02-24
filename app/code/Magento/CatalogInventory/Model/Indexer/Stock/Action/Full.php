<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

use Magento\Framework\App\ResourceConnection;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\StockFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\BatchSizeCalculatorInterface as BatchCalculator;
use Magento\Framework\Indexer\BatchProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogInventory\Model\Indexer\Stock\AbstractAction;

/**
 * Class Full reindex action
 *
 * @package Magento\CatalogInventory\Model\Indexer\Stock\Action
 */
class Full extends AbstractAction
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var BatchCalculator
     */
    private $batchSizeCalculator;

    /**
     * @var BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var array
     */
    private $memoryTablesMinRows;

    /**
     * @param ResourceConnection $resource
     * @param StockFactory $indexerFactory
     * @param ProductType $catalogProductType
     * @param CacheContext $cacheContext
     * @param EventManager $eventManager
     * @param MetadataPool|null $metadataPool
     * @param BatchCalculator|null $batchSizeCalculator
     * @param BatchProviderInterface|null $batchProvider
     * @param array $memoryTablesMinRows
     */
    public function __construct(
        ResourceConnection $resource,
        StockFactory $indexerFactory,
        ProductType $catalogProductType,
        CacheContext $cacheContext,
        EventManager $eventManager,
        MetadataPool $metadataPool = null,
        BatchCalculator $batchSizeCalculator = null,
        BatchProviderInterface $batchProvider = null,
        array $memoryTablesMinRows = []
    ) {
        parent::__construct($resource, $indexerFactory, $catalogProductType, $cacheContext, $eventManager);

        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
        $this->batchProvider = $batchProvider ?: ObjectManager::getInstance()->get(BatchProviderInterface::class);
        $this->batchSizeCalculator = $batchSizeCalculator ?: ObjectManager::getInstance()->get(BatchCalculator::class);
        $this->memoryTablesMinRows = $memoryTablesMinRows;
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
     */
    public function execute($ids = null)
    {
        try {
            $this->useIdxTable(false);
            $entityMetadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);

            $tableName = $this->_getTable('cataloginventory_stock_status');
            $columns = array_keys($this->_getConnection()->describeTable($this->_getIdxTable()));

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\AbstractIndexer $indexer */
            foreach ($this->_getTypeIndexers() as $indexer) {
                $connection = $indexer->getConnection();
                $batches = $this->batchProvider->getBatches(
                    $connection,
                    $entityMetadata->getEntityTable(),
                    $entityMetadata->getIdentifierField(),
                    $this->batchSizeCalculator->estimateBatchSize($connection, 200)
                );

                foreach ($batches as $batch) {
                    $this->clearTemporaryIndexTable();
                    // Get entity ids from batch
                    $select = $connection->select();
                    $select->distinct(true);
                    $select->from(['e' => $entityMetadata->getEntityTable()], $entityMetadata->getIdentifierField());
                    $select->where('type_id = ?', $indexer->getTypeId());

                    $entityIds = $this->batchProvider->getBatchIds($connection, $select, $batch);
                    $indexer->reindexBatch($entityIds);

                    $select = $connection->select()->from($this->_getIdxTable(), $columns);
                    $query = $select->insertFromSelect($tableName, $columns);
                    $connection->query($query);
                }
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
