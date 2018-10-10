<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Action;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

/**
 * Class Full reindex action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Full extends \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BatchSizeCalculator
     */
    private $batchSizeCalculator;

    /**
     * @var \Magento\Framework\Indexer\BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var EntityMetadataInterface
     */
    private $productMetaDataCached;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory
     */
    private $dimensionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer
     */
    private $dimensionTableMaintainer;

    /**
     * @var \Magento\Indexer\Model\ProcessManager
     */
    private $processManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory $indexerPriceFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice $defaultIndexerResource
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BatchSizeCalculator|null $batchSizeCalculator
     * @param \Magento\Framework\Indexer\BatchProviderInterface|null $batchProvider
     * @param \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher|null $activeTableSwitcher
     * @param \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory|null $dimensionCollectionFactory
     * @param \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer|null $dimensionTableMaintainer
     * @param \Magento\Indexer\Model\ProcessManager $processManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory $indexerPriceFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice $defaultIndexerResource,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BatchSizeCalculator $batchSizeCalculator = null,
        \Magento\Framework\Indexer\BatchProviderInterface $batchProvider = null,
        \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher $activeTableSwitcher = null,
        \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory $dimensionCollectionFactory = null,
        \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer $dimensionTableMaintainer = null,
        \Magento\Indexer\Model\ProcessManager $processManager = null
    ) {
        parent::__construct(
            $config,
            $storeManager,
            $currencyFactory,
            $localeDate,
            $dateTime,
            $catalogProductType,
            $indexerPriceFactory,
            $defaultIndexerResource
        );
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(
            \Magento\Framework\EntityManager\MetadataPool::class
        );
        $this->batchSizeCalculator = $batchSizeCalculator ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BatchSizeCalculator::class
        );
        $this->batchProvider = $batchProvider ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Indexer\BatchProviderInterface::class
        );
        $this->activeTableSwitcher = $activeTableSwitcher ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher::class
        );
        $this->dimensionCollectionFactory = $dimensionCollectionFactory ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory::class
        );
        $this->dimensionTableMaintainer = $dimensionTableMaintainer ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer::class
        );
        $this->processManager = $processManager ?: ObjectManager::getInstance()->get(
            \Magento\Indexer\Model\ProcessManager::class
        );
    }

    /**
     * Execute Full reindex
     *
     * @param array|int|null $ids
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids = null)
    {
        try {
            //Prepare indexer tables before full reindex
            $this->prepareTables();

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice $indexer */
            foreach ($this->getTypeIndexers(true) as $typeId => $priceIndexer) {
                if ($priceIndexer instanceof DimensionalIndexerInterface) {
                    //New price reindex mechanism
                    $this->reindexProductTypeWithDimensions($priceIndexer, $typeId);
                    continue;
                }

                $priceIndexer->getTableStrategy()->setUseIdxTable(false);

                //Old price reindex mechanism
                $this->reindexProductType($priceIndexer, $typeId);
            }

            //Final replacement of tables from replica to main
            $this->switchTables();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Prepare indexer tables before full reindex
     *
     * @return void
     * @throws \Exception
     */
    private function prepareTables()
    {
        $this->_defaultIndexerResource->getTableStrategy()->setUseIdxTable(false);

        $this->_prepareWebsiteDateTable();

        $this->truncateReplicaTables();
    }

    /**
     * Truncate replica tables by dimensions
     *
     * @return void
     * @throws \Exception
     */
    private function truncateReplicaTables()
    {
        foreach ($this->dimensionCollectionFactory->create() as $dimension) {
            $dimensionTable = $this->dimensionTableMaintainer->getMainReplicaTable($dimension);
            $this->_defaultIndexerResource->getConnection()->truncateTable($dimensionTable);
        }
    }

    /**
     * Reindex new 'Dimensional' price indexer by product type
     *
     * @param DimensionalIndexerInterface $priceIndexer
     * @param string $typeId
     *
     * @return void
     * @throws \Exception
     */
    private function reindexProductTypeWithDimensions(DimensionalIndexerInterface $priceIndexer, string $typeId)
    {
        $userFunctions = [];
        foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
            $userFunctions[] = function () use ($priceIndexer, $dimensions, $typeId) {
                return $this->reindexByBatches($priceIndexer, $dimensions, $typeId);
            };
        }
        $this->processManager->execute($userFunctions);
    }

    /**
     * Reindex new 'Dimensional' price indexer by batches
     *
     * @param DimensionalIndexerInterface $priceIndexer
     * @param array $dimensions
     * @param string $typeId
     *
     * @return void
     * @throws \Exception
     */
    private function reindexByBatches(DimensionalIndexerInterface $priceIndexer, array $dimensions, string $typeId)
    {
        foreach ($this->getBatchesForIndexer($typeId) as $batch) {
            $this->reindexByBatchWithDimensions($priceIndexer, $batch, $dimensions, $typeId);
        }
    }

    /**
     * Get batches for new 'Dimensional' price indexer
     *
     * @param string $typeId
     *
     * @return \Generator
     * @throws \Exception
     */
    private function getBatchesForIndexer(string $typeId)
    {
        $connection = $this->_defaultIndexerResource->getConnection();
        return $this->batchProvider->getBatches(
            $connection,
            $this->getProductMetaData()->getEntityTable(),
            $this->getProductMetaData()->getIdentifierField(),
            $this->batchSizeCalculator->estimateBatchSize(
                $connection,
                $typeId
            )
        );
    }

    /**
     * Reindex by batch for new 'Dimensional' price indexer
     *
     * @param DimensionalIndexerInterface $priceIndexer
     * @param array $batch
     * @param array $dimensions
     * @param string $typeId
     *
     * @return void
     * @throws \Exception
     */
    private function reindexByBatchWithDimensions(
        DimensionalIndexerInterface $priceIndexer,
        array $batch,
        array $dimensions,
        string $typeId
    ) {
        $entityIds = $this->getEntityIdsFromBatch($typeId, $batch);

        if (!empty($entityIds)) {
            $this->dimensionTableMaintainer->createMainTmpTable($dimensions);
            $temporaryTable = $this->dimensionTableMaintainer->getMainTmpTable($dimensions);
            $this->_emptyTable($temporaryTable);

            $priceIndexer->executeByDimensions($dimensions, \SplFixedArray::fromArray($entityIds, false));

            // Sync data from temp table to index table
            $this->_insertFromTable(
                $temporaryTable,
                $this->dimensionTableMaintainer->getMainReplicaTable($dimensions)
            );
        }
    }

    /**
     * Reindex old price indexer by product type
     *
     * @param PriceInterface $priceIndexer
     * @param string $typeId
     *
     * @return void
     * @throws \Exception
     */
    private function reindexProductType(PriceInterface $priceIndexer, string $typeId)
    {
        foreach ($this->getBatchesForIndexer($typeId) as $batch) {
            $this->reindexBatch($priceIndexer, $batch, $typeId);
        }
    }

    /**
     * Reindex by batch for old price indexer
     *
     * @param PriceInterface $priceIndexer
     * @param array $batch
     * @param string $typeId
     *
     * @return void
     * @throws \Exception
     */
    private function reindexBatch(PriceInterface $priceIndexer, array $batch, string $typeId)
    {
        $entityIds = $this->getEntityIdsFromBatch($typeId, $batch);

        if (!empty($entityIds)) {
            // Temporary table will created if not exists
            $idxTableName = $this->_defaultIndexerResource->getIdxTable();
            $this->_emptyTable($idxTableName);

            if ($priceIndexer->getIsComposite()) {
                $this->_copyRelationIndexData($entityIds);
            }

            // Reindex entities by id
            $priceIndexer->reindexEntity($entityIds);

            // Sync data from temp table to index table
            $this->_insertFromTable($idxTableName, $this->getReplicaTable());

            // Drop temporary index table
            $this->_defaultIndexerResource->getConnection()->dropTable($idxTableName);
        }
    }

    /**
     * Get Entity Ids from batch
     *
     * @param string $typeId
     * @param array $batch
     *
     * @return array
     * @throws \Exception
     */
    private function getEntityIdsFromBatch(string $typeId, array $batch)
    {
        $connection = $this->_defaultIndexerResource->getConnection();

        // Get entity ids from batch
        $select = $connection
            ->select()
            ->distinct(true)
            ->from(
                ['e' => $this->getProductMetaData()->getEntityTable()],
                $this->getProductMetaData()->getIdentifierField()
            )
            ->where('type_id = ?', $typeId);

        return $this->batchProvider->getBatchIds($connection, $select, $batch);
    }

    /**
     * Get product meta data
     *
     * @return EntityMetadataInterface
     * @throws \Exception
     */
    private function getProductMetaData()
    {
        if ($this->productMetaDataCached === null) {
            $this->productMetaDataCached = $this->metadataPool->getMetadata(ProductInterface::class);
        }

        return $this->productMetaDataCached;
    }

    /**
     * Get replica table
     *
     * @return string
     * @throws \Exception
     */
    private function getReplicaTable()
    {
        return $this->activeTableSwitcher->getAdditionalTableName(
            $this->_defaultIndexerResource->getMainTable()
        );
    }

    /**
     * Replacement of tables from replica to main
     *
     * @return void
     */
    private function switchTables()
    {
        // Switch dimension tables
        $mainTablesByDimension = [];

        foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
            $mainTablesByDimension[] = $this->dimensionTableMaintainer->getMainTable($dimensions);

            //Move data from indexers with old realisation
            $this->moveDataFromReplicaTableToReplicaTables($dimensions);
        }

        if (count($mainTablesByDimension) > 0) {
            $this->activeTableSwitcher->switchTable(
                $this->_defaultIndexerResource->getConnection(),
                $mainTablesByDimension
            );
        }
    }

    /**
     * Move data from old price indexer mechanism to new indexer mechanism by dimensions.
     * Used only for backward compatibility
     *
     * @param array $dimensions
     *
     * @return void
     */
    private function moveDataFromReplicaTableToReplicaTables(array $dimensions)
    {
        if (!$dimensions) {
            return;
        }
        $select = $this->dimensionTableMaintainer->getConnection()->select()->from(
            $this->dimensionTableMaintainer->getMainReplicaTable([])
        );

        $check = clone $select;
        $check->reset('columns')->columns('count(*)');

        if (!$this->dimensionTableMaintainer->getConnection()->query($check)->fetchColumn()) {
            return;
        }

        $replicaTablesByDimension = $this->dimensionTableMaintainer->getMainReplicaTable($dimensions);

        foreach ($dimensions as $dimension) {
            if ($dimension->getName() === WebsiteDimensionProvider::DIMENSION_NAME) {
                $select->where('website_id = ?', $dimension->getValue());
            }
            if ($dimension->getName() === CustomerGroupDimensionProvider::DIMENSION_NAME) {
                $select->where('customer_group_id = ?', $dimension->getValue());
            }
        }

        $this->dimensionTableMaintainer->getConnection()->query(
            $this->dimensionTableMaintainer->getConnection()->insertFromSelect(
                $select,
                $replicaTablesByDimension,
                [],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            )
        );
    }

    /**
     * @deprecated
     *
     * @inheritdoc
     */
    protected function getIndexTargetTable()
    {
        return $this->activeTableSwitcher->getAdditionalTableName($this->_defaultIndexerResource->getMainTable());
    }
}
