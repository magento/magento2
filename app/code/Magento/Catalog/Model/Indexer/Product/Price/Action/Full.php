<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Price\Action;

use Magento\Catalog\Model\Indexer\Product\Price\AbstractAction;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BatchSizeCalculator;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceInterface;
<<<<<<< HEAD
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
=======
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\BatchIterator;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\BatchProviderInterface;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Indexer\Model\ProcessManager;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Store\Model\StoreManagerInterface;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

/**
 * Class Full reindex action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Full extends AbstractAction
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var BatchSizeCalculator
     */
    private $batchSizeCalculator;

    /**
     * @var BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var EntityMetadataInterface
     */
    private $productMetaDataCached;

    /**
<<<<<<< HEAD
     * @var \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory
=======
     * @var DimensionCollectionFactory
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $dimensionCollectionFactory;

    /**
<<<<<<< HEAD
     * @var \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer
=======
     * @var TableMaintainer
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $dimensionTableMaintainer;

    /**
<<<<<<< HEAD
     * @var \Magento\Indexer\Model\ProcessManager
=======
     * @var ProcessManager
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $processManager;

    /**
<<<<<<< HEAD
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
=======
     * @var QueryGenerator|null
     */
    private $batchQueryGenerator;

    /**
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param Type $catalogProductType
     * @param Factory $indexerPriceFactory
     * @param DefaultPrice $defaultIndexerResource
     * @param MetadataPool|null $metadataPool
     * @param BatchSizeCalculator|null $batchSizeCalculator
     * @param BatchProviderInterface|null $batchProvider
     * @param ActiveTableSwitcher|null $activeTableSwitcher
     * @param DimensionCollectionFactory|null $dimensionCollectionFactory
     * @param TableMaintainer|null $dimensionTableMaintainer
     * @param ProcessManager $processManager
     * @param QueryGenerator|null $batchQueryGenerator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        TimezoneInterface $localeDate,
        DateTime $dateTime,
        Type $catalogProductType,
        Factory $indexerPriceFactory,
        DefaultPrice $defaultIndexerResource,
        MetadataPool $metadataPool = null,
        BatchSizeCalculator $batchSizeCalculator = null,
        BatchProviderInterface $batchProvider = null,
        ActiveTableSwitcher $activeTableSwitcher = null,
        DimensionCollectionFactory $dimensionCollectionFactory = null,
        TableMaintainer $dimensionTableMaintainer = null,
        ProcessManager $processManager = null,
        QueryGenerator $batchQueryGenerator = null
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
            MetadataPool::class
        );
        $this->batchSizeCalculator = $batchSizeCalculator ?: ObjectManager::getInstance()->get(
            BatchSizeCalculator::class
        );
        $this->batchProvider = $batchProvider ?: ObjectManager::getInstance()->get(
            BatchProviderInterface::class
        );
        $this->activeTableSwitcher = $activeTableSwitcher ?: ObjectManager::getInstance()->get(
            ActiveTableSwitcher::class
        );
        $this->dimensionCollectionFactory = $dimensionCollectionFactory ?: ObjectManager::getInstance()->get(
            DimensionCollectionFactory::class
        );
        $this->dimensionTableMaintainer = $dimensionTableMaintainer ?: ObjectManager::getInstance()->get(
            TableMaintainer::class
        );
<<<<<<< HEAD
        $this->dimensionCollectionFactory = $dimensionCollectionFactory ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory::class
        );
        $this->dimensionTableMaintainer = $dimensionTableMaintainer ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer::class
        );
        $this->processManager = $processManager ?: ObjectManager::getInstance()->get(
            \Magento\Indexer\Model\ProcessManager::class
        );
=======
        $this->processManager = $processManager ?: ObjectManager::getInstance()->get(
            ProcessManager::class
        );
        $this->batchQueryGenerator = $batchQueryGenerator ?? ObjectManager::getInstance()->get(QueryGenerator::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * Execute Full reindex
     *
     * @param array|int|null $ids
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids = null): void
    {
        try {
            //Prepare indexer tables before full reindex
            $this->prepareTables();

<<<<<<< HEAD
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice $indexer */
=======
            /** @var DefaultPrice $indexer */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
    private function prepareTables()
=======
    private function prepareTables(): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $this->_defaultIndexerResource->getTableStrategy()->setUseIdxTable(false);

        $this->_prepareWebsiteDateTable();
<<<<<<< HEAD

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

=======

        $this->truncateReplicaTables();
    }

    /**
     * Truncate replica tables by dimensions
     *
     * @return void
     * @throws \Exception
     */
    private function truncateReplicaTables(): void
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
    private function reindexProductTypeWithDimensions(DimensionalIndexerInterface $priceIndexer, string $typeId): void
    {
        $userFunctions = [];
        foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
            $userFunctions[] = function () use ($priceIndexer, $dimensions, $typeId) {
                $this->reindexByBatches($priceIndexer, $dimensions, $typeId);
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
    private function reindexByBatches(
        DimensionalIndexerInterface $priceIndexer,
        array $dimensions,
        string $typeId
    ): void {
        foreach ($this->getBatchesForIndexer($typeId) as $batch) {
            $this->reindexByBatchWithDimensions($priceIndexer, $batch, $dimensions);
        }
    }

    /**
     * Get batches for new 'Dimensional' price indexer
     *
     * @param string $typeId
     *
     * @return BatchIterator
     * @throws \Exception
     */
    private function getBatchesForIndexer(string $typeId): BatchIterator
    {
        $connection = $this->_defaultIndexerResource->getConnection();
        $entityMetadata = $this->getProductMetaData();
        $select = $connection->select();
        $select->distinct(true);
        $select->from(['e' => $entityMetadata->getEntityTable()], $entityMetadata->getIdentifierField());

        return $this->batchQueryGenerator->generate(
            $this->getProductMetaData()->getIdentifierField(),
            $select,
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
     * @param Select $batchQuery
     * @param array $dimensions
     *
     * @return void
     * @throws \Exception
     */
    private function reindexByBatchWithDimensions(
        DimensionalIndexerInterface $priceIndexer,
        Select $batchQuery,
        array $dimensions
    ): void {
        $entityIds = $this->getEntityIdsFromBatch($batchQuery);

        if (!empty($entityIds)) {
            $this->dimensionTableMaintainer->createMainTmpTable($dimensions);
            $temporaryTable = $this->dimensionTableMaintainer->getMainTmpTable($dimensions);
            $this->_emptyTable($temporaryTable);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
    private function reindexProductType(PriceInterface $priceIndexer, string $typeId)
    {
        foreach ($this->getBatchesForIndexer($typeId) as $batch) {
            $this->reindexBatch($priceIndexer, $batch, $typeId);
=======
    private function reindexProductType(PriceInterface $priceIndexer, string $typeId): void
    {
        foreach ($this->getBatchesForIndexer($typeId) as $batch) {
            $this->reindexBatch($priceIndexer, $batch);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        }
    }

    /**
     * Reindex by batch for old price indexer
     *
     * @param PriceInterface $priceIndexer
<<<<<<< HEAD
     * @param array $batch
     * @param string $typeId
     *
     * @return void
     * @throws \Exception
     */
    private function reindexBatch(PriceInterface $priceIndexer, array $batch, string $typeId)
    {
        $entityIds = $this->getEntityIdsFromBatch($typeId, $batch);
=======
     * @param Select $batch
     * @return void
     * @throws \Exception
     */
    private function reindexBatch(PriceInterface $priceIndexer, Select $batch): void
    {
        $entityIds = $this->getEntityIdsFromBatch($batch);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

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
<<<<<<< HEAD
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
=======
     * @param Select $batch
     * @return array
     * @throws \Exception
     */
    private function getEntityIdsFromBatch(Select $batch): array
    {
        $connection = $this->_defaultIndexerResource->getConnection();

        return $connection->fetchCol($batch);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * Get product meta data
     *
     * @return EntityMetadataInterface
     * @throws \Exception
     */
<<<<<<< HEAD
    private function getProductMetaData()
=======
    private function getProductMetaData(): EntityMetadataInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
    private function getReplicaTable()
=======
    private function getReplicaTable(): string
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return $this->activeTableSwitcher->getAdditionalTableName(
            $this->_defaultIndexerResource->getMainTable()
        );
    }

    /**
     * Replacement of tables from replica to main
     *
     * @return void
<<<<<<< HEAD
     */
    private function switchTables()
=======
     * @throws \Zend_Db_Statement_Exception
     */
    private function switchTables(): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
     * Used only for backward compatibility
     *
     * @param array $dimensions
     *
     * @return void
     */
    private function moveDataFromReplicaTableToReplicaTables(array $dimensions)
=======
     *
     * Used only for backward compatibility
     *
     * @param array $dimensions
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    private function moveDataFromReplicaTableToReplicaTables(array $dimensions): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
=======
                AdapterInterface::INSERT_ON_DUPLICATE
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            )
        );
    }

    /**
<<<<<<< HEAD
     * @deprecated
     *
     * @inheritdoc
=======
     * Retrieves the index table that should be used
     *
     * @deprecated
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    protected function getIndexTargetTable(): string
    {
        return $this->activeTableSwitcher->getAdditionalTableName($this->_defaultIndexerResource->getMainTable());
    }
}
