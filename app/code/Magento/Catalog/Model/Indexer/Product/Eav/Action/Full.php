<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Eav\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\BatchIteratorInterface;
use Magento\Framework\DB\Query\Generator as QueryGenerator;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\BatchProviderInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Full reindex action
<<<<<<< HEAD
=======
 *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Full extends AbstractAction
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var BatchProviderInterface
     */
    private $batchProvider;

    /**
     * @var BatchSizeCalculator
     */
    private $batchSizeCalculator;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
<<<<<<< HEAD
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
=======
     * @var ScopeConfigInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $scopeConfig;

    /**
<<<<<<< HEAD
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory $eavDecimalFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory $eavSourceFactory
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     * @param \Magento\Framework\Indexer\BatchProviderInterface|null $batchProvider
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator $batchSizeCalculator
     * @param ActiveTableSwitcher|null $activeTableSwitcher
     * @param \Magento\Framework\App\Config\ScopeConfigInterface|null $scopeConfig
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory $eavDecimalFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory $eavSourceFactory,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null,
        \Magento\Framework\Indexer\BatchProviderInterface $batchProvider = null,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator $batchSizeCalculator = null,
        ActiveTableSwitcher $activeTableSwitcher = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig = null
    ) {
        $this->scopeConfig = $scopeConfig ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
        parent::__construct($eavDecimalFactory, $eavSourceFactory, $scopeConfig);
        $this->metadataPool = $metadataPool ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\EntityManager\MetadataPool::class
=======
     * @var QueryGenerator|null
     */
    private $batchQueryGenerator;

    /**
     * @param DecimalFactory $eavDecimalFactory
     * @param SourceFactory $eavSourceFactory
     * @param MetadataPool|null $metadataPool
     * @param BatchProviderInterface|null $batchProvider
     * @param BatchSizeCalculator $batchSizeCalculator
     * @param ActiveTableSwitcher|null $activeTableSwitcher
     * @param ScopeConfigInterface|null $scopeConfig
     * @param QueryGenerator|null $batchQueryGenerator
     */
    public function __construct(
        DecimalFactory $eavDecimalFactory,
        SourceFactory $eavSourceFactory,
        MetadataPool $metadataPool = null,
        BatchProviderInterface $batchProvider = null,
        BatchSizeCalculator $batchSizeCalculator = null,
        ActiveTableSwitcher $activeTableSwitcher = null,
        ScopeConfigInterface $scopeConfig = null,
        QueryGenerator $batchQueryGenerator = null
    ) {
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(
            ScopeConfigInterface::class
        );
        parent::__construct($eavDecimalFactory, $eavSourceFactory, $scopeConfig);
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(
            MetadataPool::class
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );
        $this->batchProvider = $batchProvider ?: ObjectManager::getInstance()->get(
            BatchProviderInterface::class
        );
        $this->batchSizeCalculator = $batchSizeCalculator ?: ObjectManager::getInstance()->get(
            BatchSizeCalculator::class
        );
        $this->activeTableSwitcher = $activeTableSwitcher ?: ObjectManager::getInstance()->get(
            ActiveTableSwitcher::class
        );
        $this->batchQueryGenerator = $batchQueryGenerator ?: ObjectManager::getInstance()->get(
            QueryGenerator::class
        );
    }

    /**
     * Execute Full reindex
     *
     * @param array|int|null $ids
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($ids = null): void
    {
        if (!$this->isEavIndexerEnabled()) {
            return;
        }
        try {
            foreach ($this->getIndexers() as $indexerName => $indexer) {
                $connection = $indexer->getConnection();
                $mainTable = $this->activeTableSwitcher->getAdditionalTableName($indexer->getMainTable());
                $connection->truncateTable($mainTable);
                $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);

                $select = $connection->select();
                $select->distinct(true);
                $select->from(['e' => $entityMetadata->getEntityTable()], $entityMetadata->getIdentifierField());

                $batchQueries = $this->batchQueryGenerator->generate(
                    $entityMetadata->getIdentifierField(),
                    $select,
                    $this->batchSizeCalculator->estimateBatchSize($connection, $indexerName),
                    BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
                );

                foreach ($batchQueries as $query) {
                    $entityIds = $connection->fetchCol($query);
                    if (!empty($entityIds)) {
                        $indexer->reindexEntities($this->processRelations($indexer, $entityIds, true));
                        $this->syncData($indexer, $mainTable);
                    }
                }
                $this->activeTableSwitcher->switchTable($indexer->getConnection(), [$indexer->getMainTable()]);
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @inheritdoc
     */
    protected function syncData($indexer, $destinationTable, $ids = null): void
    {
        $connection = $indexer->getConnection();
        $connection->beginTransaction();
        try {
            $sourceTable = $indexer->getIdxTable();
            $sourceColumns = array_keys($connection->describeTable($sourceTable));
            $targetColumns = array_keys($connection->describeTable($destinationTable));
            $select = $connection->select()->from($sourceTable, $sourceColumns);
            $query = $connection->insertFromSelect(
                $select,
                $destinationTable,
                $targetColumns,
                AdapterInterface::INSERT_ON_DUPLICATE
            );
            $connection->query($query);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * Get EAV indexer status
     *
     * @return bool
     */
    private function isEavIndexerEnabled(): bool
    {
        $eavIndexerStatus = $this->scopeConfig->getValue(
            self::ENABLE_EAV_INDEXER,
<<<<<<< HEAD
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
=======
            ScopeInterface::SCOPE_STORE
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );

        return (bool)$eavIndexerStatus;
    }
}
