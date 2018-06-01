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
use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;
use Magento\Framework\Indexer\DimensionProviderInterface;

/**
 * Class Full reindex action
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
     * @var \Magento\Catalog\Model\Indexer\Product\Price\DimensionProviderFactory
     */
    private $dimensionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer
     */
    private $dimensionTableMaintainer;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer
     */
    private $configReader;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

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
     * @param \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer|null $dimensionTableMaintainer
     *
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
        \Magento\Catalog\Model\Indexer\Product\Price\DimensionProviderFactory $dimensionCollectionFactory = null,
        \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer $dimensionTableMaintainer = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $configReader = null,
        \Magento\Framework\App\ResourceConnection $resource = null,
        $connectionName = 'indexer'
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
            \Magento\Catalog\Model\Indexer\Product\Price\DimensionProviderFactory::class
        );
        $this->dimensionTableMaintainer = $dimensionTableMaintainer ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer::class
        );
        $this->configReader = $configReader ?: ObjectManager::getInstance()->get(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
        $this->resource = $resource ?? ObjectManager::getInstance()->get(
                \Magento\Framework\App\ResourceConnection::class
            );
        $this->connectionName = $connectionName;
    }

    /**
     * Execute Full reindex
     *
     * @param array|int|null $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids = null)
    {
        try {
            $this->prepareTables();

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice $indexer */
            foreach ($this->getTypeIndexers() as $typeId => $priceIndexer) {

                if ($priceIndexer instanceof DimensionalIndexerInterface) {
                    $this->reindexProductTypeWithDimensions($priceIndexer, $typeId);
                    continue;
                }

                $priceIndexer->getTableStrategy()->setUseIdxTable(false);
                $this->reindexProductType($priceIndexer, $typeId);
            }

            $this->switchTables();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    private function prepareTables()
    {
        $this->_defaultIndexerResource->getTableStrategy()->setUseIdxTable(false);

        $this->_prepareWebsiteDateTable();

        // Prepare replica table for indexation.
        $this->_defaultIndexerResource->getConnection()->truncateTable($this->getReplicaTable());
    }

    private function switchTables()
    {
        $currentMode = $this->configReader
            ->getValue(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE) ?: ModeSwitcher::INPUT_KEY_NONE;

        /** @var DimensionProviderInterface $dimensionsProviders */
        $dimensionsProviders = $this->dimensionCollectionFactory->createByMode($currentMode);

        // Switch dimension tables
        $dimensionTables = [];

        foreach ($dimensionsProviders as $dimension) {
            $dimensionTables[] = $this->dimensionTableMaintainer->getMainTable($dimension);
        }

        if (count($dimensionTables) > 0) {
            $this->activeTableSwitcher->switchTable(
                $this->_defaultIndexerResource->getConnection(),
                $dimensionTables
            );
        }
    }

    private function reindexProductType(PriceInterface $priceIndexer, string $typeId)
    {
        foreach ($this->getBatchesForIndexer($typeId) as $batch) {
            $this->reindexBatch($priceIndexer, $batch, $typeId);
        }
    }

    private function reindexProductTypeWithDimensions(DimensionalIndexerInterface $priceIndexer, string $typeId)
    {
        $currentMode = $this->configReader
            ->getValue(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE) ?: ModeSwitcher::INPUT_KEY_NONE;

        /** @var DimensionProviderInterface $dimensionsProviders */
        $dimensionsProviders = $this->dimensionCollectionFactory->createByMode($currentMode);

        foreach ($dimensionsProviders as $dimensions) {
            $this->dimensionTableMaintainer->getConnection()->truncateTable(
                $this->dimensionTableMaintainer->getMainReplicaTable($dimensions)
            );
            $this->reindexDimensions($priceIndexer, $dimensions, $typeId);
        }
    }

    private function reindexDimensions(DimensionalIndexerInterface $priceIndexer, array $dimensions, string $typeId)
    {
        foreach ($this->getBatchesForIndexer($typeId) as $batch) {
            $this->reindexBatchWithinDimension($priceIndexer, $batch, $dimensions, $typeId);
        }
    }

    private function reindexBatch(PriceInterface $priceIndexer, array $batch, string $typeId)
    {
        $entityIds = $this->getEntityIdsFromBatch($typeId, $batch);

        if (!empty($entityIds)) {
            // Temporary table will created if not exists
            $idxTableName = $this->_defaultIndexerResource->getIdxTable();
            $this->_emptyTable($idxTableName);

            $this->_prepareTierPriceIndex($entityIds);

            // Reindex entities by id
            $priceIndexer->reindexEntity($entityIds);

            // Sync data from temp table to index table
            $this->_insertFromTable($idxTableName, $this->getReplicaTable());

            // Drop temporary index table
            $priceIndexer->getConnection()->dropTable($idxTableName);
        }
    }

    private function reindexBatchWithinDimension(DimensionalIndexerInterface $priceIndexer, array $batch, array $dimensions, string $typeId)
    {
        $entityIds = $this->getEntityIdsFromBatch($typeId, $batch);

        if (!empty($entityIds)) {
            $this->dimensionTableMaintainer->createMainTmpTable($dimensions);
            $temporaryTable = $this->dimensionTableMaintainer->getMainTmpTable($dimensions);
            $this->_emptyTable($temporaryTable);

            // TODO: will be handled in separate task
            // if ($priceIndexer->getIsComposite()) {
            //    $this->_copyRelationIndexData($entityIds);
            // }

            // TODO: handle inside index model and move index fulfilment into index model
            $this->_prepareTierPriceIndex($entityIds);

            $priceIndexer->executeByDimension($dimensions, \SplFixedArray::fromArray($entityIds));

            // Sync data from temp table to index table
            $this->_insertFromTable(
                $temporaryTable,
                $this->dimensionTableMaintainer->getMainReplicaTable($dimensions)
            );
        }
    }

    private function getEntityIdsFromBatch(string $typeId, array $batch)
    {
        $connection = $this->resource->getConnection($this->connectionName);
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

    private function getProductMetaData()
    {
        if ($this->productMetaDataCached === null) {
            $this->productMetaDataCached = $this->metadataPool->getMetadata(ProductInterface::class);
        }

        return $this->productMetaDataCached;
    }

    private function getReplicaTable()
    {
        return $this->activeTableSwitcher->getAdditionalTableName(
            $this->_defaultIndexerResource->getMainTable()
        );
    }

    private function getBatchesForIndexer(string $typeId)
    {
        $connection = $this->resource->getConnection($this->connectionName);
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
     * @inheritdoc
     */
    protected function getIndexTargetTable()
    {
        return $this->activeTableSwitcher->getAdditionalTableName($this->_defaultIndexerResource->getMainTable());
    }
}
