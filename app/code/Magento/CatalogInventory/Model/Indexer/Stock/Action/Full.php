<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

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
use Magento\Indexer\Model\ResourceModel\FrontendResource;

/**
 * Class Full reindex action
 *
 * @package Magento\CatalogInventory\Model\Indexer\Stock\Action
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
     * @param ResourceConnection $resource
     * @param StockFactory $indexerFactory
     * @param ProductType $catalogProductType
     * @param CacheContext $cacheContext
     * @param EventManager $eventManager
     * @param null|\Magento\Indexer\Model\ResourceModel\FrontendResource $indexerStockFrontendResource
     * @param MetadataPool|null $metadataPool
     * @param BatchSizeManagementInterface|null $batchSizeManagement
     * @param BatchProviderInterface|null $batchProvider
     * @param array $batchRowsCount
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resource,
        StockFactory $indexerFactory,
        ProductType $catalogProductType,
        CacheContext $cacheContext,
        EventManager $eventManager,
        FrontendResource $indexerStockFrontendResource = null,
        MetadataPool $metadataPool = null,
        BatchSizeManagementInterface $batchSizeManagement = null,
        BatchProviderInterface $batchProvider = null,
        array $batchRowsCount = []
    ) {
        parent::__construct(
            $resource,
            $indexerFactory,
            $catalogProductType,
            $cacheContext,
            $eventManager,
            $indexerStockFrontendResource
        );

        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
        $this->batchProvider = $batchProvider ?: ObjectManager::getInstance()->get(BatchProviderInterface::class);
        $this->batchSizeManagement = $batchSizeManagement ?: ObjectManager::getInstance()->get(
            \Magento\CatalogInventory\Model\Indexer\Stock\BatchSizeManagement::class
        );
        $this->batchRowsCount = $batchRowsCount;
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
            $this->_cleanMainTable();

            $entityMetadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);

            $columns = array_keys($this->_getConnection()->describeTable($this->_getIdxTable()));

            /** @var \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock $indexer */
            foreach ($this->_getTypeIndexers() as $indexer) {
                $indexer->setActionType(self::ACTION_TYPE);
                $connection = $indexer->getConnection();
                $tableName = $indexer->getMainTable();

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
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
