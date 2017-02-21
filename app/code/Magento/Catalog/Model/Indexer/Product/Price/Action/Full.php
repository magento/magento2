<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Action;

/**
 * Class Full reindex action
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Full extends \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
{
    /**
     * @var array
     */
    private $memoryTablesMinRows;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\Indexer\BatchSizeCalculatorInterface
     */
    private $batchSizeCalculator;

    /**
     * @var \Magento\Framework\Indexer\BatchProviderInterface
     */
    private $batchProvider;

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
     * @param \Magento\Framework\Indexer\BatchSizeCalculatorInterface|null $batchSizeCalculator
     * @param \Magento\Framework\Indexer\BatchProviderInterface|null $batchProvider
     * @param array $memoryTablesMinRows
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
        \Magento\Framework\Indexer\BatchSizeCalculatorInterface $batchSizeCalculator = null,
        \Magento\Framework\Indexer\BatchProviderInterface $batchProvider = null,
        array $memoryTablesMinRows = []
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->metadataPool = $metadataPool ?: $objectManager->get(
            \Magento\Framework\EntityManager\MetadataPool::class
        );
        $this->batchSizeCalculator = $batchSizeCalculator ?: $objectManager->get(
            \Magento\Framework\Indexer\BatchSizeCalculatorInterface::class
        );
        $this->batchProvider = $batchProvider ?: $objectManager->get(
            \Magento\Framework\Indexer\BatchProviderInterface::class
        );
        $this->memoryTablesMinRows = $memoryTablesMinRows;
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
            $this->_defaultIndexerResource->beginTransaction();
            $this->_defaultIndexerResource->getTableStrategy()->setUseIdxTable(false);
            $this->_prepareWebsiteDateTable();
            $this->_emptyTable($this->_defaultIndexerResource->getIdxTable());
            $this->emptyPriceIndexTable();

            $entityMetadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\AbstractIndexer $indexer */
            foreach ($this->getTypeIndexers() as $indexer) {

                $memoryTableMinRows = isset($this->memoryTablesMinRows[$indexer->getTypeId()])
                    ? $this->memoryTablesMinRows[$indexer->getTypeId()]
                    : $this->memoryTablesMinRows['default'];

                $connection = $indexer->getConnection();
                $batches = $this->batchProvider->getBatches(
                    $connection,
                    $entityMetadata->getEntityTable(),
                    $entityMetadata->getIdentifierField(),
                    $this->batchSizeCalculator->estimateBatchSize($connection, $memoryTableMinRows)
                );

                foreach ($batches as $batch) {
                    // Get entity ids from batch
                    $select = $connection->select();
                    $select->distinct(true);
                    $select->from(['e' => $entityMetadata->getEntityTable()], $entityMetadata->getIdentifierField());
                    $select->where('type_id = ?', $indexer->getTypeId());

                    $entityIds = $this->batchProvider->getBatchIds($connection, $select, $batch);

                    if (!empty($entityIds)) {
                        $this->_emptyTable($this->_defaultIndexerResource->getIdxTable());
                        if ($indexer->getIsComposite()) {
                            $this->_copyRelationIndexData($entityIds);
                        }
                        $this->_prepareTierPriceIndex($entityIds);
                        // Reindex entities by id
                        $indexer->reindexEntity($entityIds);

                        // Sync data from temp table to index table
                        $this->_insertFromTable(
                            $this->_defaultIndexerResource->getIdxTable(),
                            $this->_defaultIndexerResource->getTable('catalog_product_index_price')
                        );
                    }
                }
            }
            $this->_defaultIndexerResource->commit();
        } catch (\Exception $e) {
            $this->_defaultIndexerResource->rollBack();
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Remove price index data
     *
     * Remove all price data from index table for current website
     * @return void
     */
    private function emptyPriceIndexTable()
    {
        $select = $this->_connection->select()->from(
            ['index_price' => $this->_defaultIndexerResource->getTable('catalog_product_index_price')],
            null
        )->joinLeft(
            ['ip_tmp' => $this->_defaultIndexerResource->getIdxTable()],
            'index_price.entity_id = ip_tmp.entity_id AND index_price.website_id = ip_tmp.website_id',
            []
        )->where(
            'ip_tmp.entity_id IS NULL'
        );
        $sql = $select->deleteFromSelect('index_price');
        $this->_connection->query($sql);
    }
}
