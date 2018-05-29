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
        \Magento\Framework\App\Config\ScopeConfigInterface $configReader = null
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
            foreach ($this->getTypeIndexers() as $priceIndexer) {
                $priceIndexer->getTableStrategy()->setUseIdxTable(false);

                if ($priceIndexer instanceof DimensionalIndexerInterface) {
                    $this->reindexProductTypeWithDimensions($priceIndexer);
                    continue;
                }

                $this->reindexProductType($priceIndexer);
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

    private function reindexProductType(PriceInterface $priceIndexer)
    {
        foreach ($this->getBatchesForIndexer($priceIndexer) as $batch) {
            $this->reindexBatch($priceIndexer, $batch);
        }
    }

    private function reindexProductTypeWithDimensions(PriceInterface $priceIndexer)
    {
        /** @var DimensionProviderInterface $dimensionsProviders */
        $dimensionsProviders = $this->dimensionCollectionFactory->createByMode(
            ModeSwitcher::INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP
        );

        foreach ($dimensionsProviders as $dimension) {
            $this->reindexDimensions($priceIndexer, $dimension);
        }
    }

    private function reindexDimensions(PriceInterface $priceIndexer, array $dimension)
    {
        foreach ($this->getBatchesForIndexer($priceIndexer) as $batch) {
            $this->reindexBatchWithinDimension($priceIndexer, $batch, $dimension);
        }
    }

    private function reindexBatch(PriceInterface $priceIndexer, array $batch)
    {
        $entityIds = $this->getEntityIdsFromBatch($priceIndexer, $batch);

        if (!empty($entityIds)) {
            // Temporary table will created if not exists
            $idxTableName = $this->_defaultIndexerResource->getIdxTable();
            $this->_emptyTable($idxTableName);

            if ($priceIndexer->getIsComposite()) {
                $this->_copyRelationIndexData($entityIds);
            }
            $this->_prepareTierPriceIndex($entityIds);

            // Reindex entities by id
            $priceIndexer->reindexEntity($entityIds);

            // Sync data from temp table to index table
            $this->_insertFromTable($idxTableName, $this->getReplicaTable());

            // Drop temporary index table
            $priceIndexer->getConnection()->dropTable($idxTableName);
        }
    }

    private function reindexBatchWithinDimension(DimensionalIndexerInterface $priceIndexer, array $batch, array $dimensions)
    {
        $entityIds = $this->getEntityIdsFromBatch($priceIndexer, $batch);

        if (!empty($entityIds)) {
            // Temporary table will created if not exists
//            $idxTableName = $this->_defaultIndexerResource->getIdxTableWithinDimension($dimensions);
            $idxTableName = $this->_defaultIndexerResource->getIdxTable(); # TODO: replace with appropriate method according to dimension
            $this->_emptyTable($idxTableName);

            if ($priceIndexer->getIsComposite()) {
                $this->_copyRelationIndexData($entityIds);
            }
            $this->_prepareTierPriceIndex($entityIds);

            $priceIndexer->reindexByDimensions($entityIds, $dimensions);

            // Sync data from temp table to index table
            $this->_insertFromTable($idxTableName, $this->dimensionTableMaintainer->getMainReplicaTable($dimensions));

            // Drop temporary index table
            $priceIndexer->getConnection()->dropTable($idxTableName);
        }
    }

    private function getEntityIdsFromBatch(PriceInterface $priceIndexer, array $batch)
    {
        // Get entity ids from batch
        $select = $priceIndexer->getConnection()
            ->select()
            ->distinct(true)
            ->from(
                ['e' => $this->getProductMetaData()->getEntityTable()],
                $this->getProductMetaData()->getIdentifierField()
            )
            ->where('type_id = ?', $priceIndexer->getTypeId());

        return $this->batchProvider->getBatchIds($priceIndexer->getConnection(), $select, $batch);
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

    private function getBatchesForIndexer(PriceInterface $priceIndexer)
    {
        return $this->batchProvider->getBatches(
            $priceIndexer->getConnection(),
            $this->getProductMetaData()->getEntityTable(),
            $this->getProductMetaData()->getIdentifierField(),
            $this->batchSizeCalculator->estimateBatchSize(
                $priceIndexer->getConnection(),
                $priceIndexer->getTypeId()
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
