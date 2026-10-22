<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BasePriceModifier;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CustomOptionPriceModifier;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Configurable Products Price Indexer Resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable implements DimensionalIndexerInterface
{
    /**
     * @var BaseFinalPrice
     */
    private $baseFinalPrice;

    /**
     * @var IndexTableStructureFactory
     */
    private $indexTableStructureFactory;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var bool
     */
    private $fullReindexAction;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var BasePriceModifier
     */
    private $basePriceModifier;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var BaseSelectProcessorInterface
     */
    private $baseSelectProcessor;

    /**
     * @var OptionsIndexerInterface
     */
    private $optionsIndexer;

    /**
     * @var CustomOptionPriceModifier
     */
    private $customOptionPriceModifier;

    /**
     * @param BaseFinalPrice $baseFinalPrice
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param MetadataPool $metadataPool
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param BasePriceModifier $basePriceModifier
     * @param bool $fullReindexAction
     * @param string $connectionName
     * @param ScopeConfigInterface|null $scopeConfig
     * @param BaseSelectProcessorInterface|null $baseSelectProcessor
     * @param OptionsIndexerInterface|null $optionsIndexer
     * @param CustomOptionPriceModifier|null $customOptionPriceModifier
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        BaseFinalPrice $baseFinalPrice,
        IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        MetadataPool $metadataPool,
        \Magento\Framework\App\ResourceConnection $resource,
        BasePriceModifier $basePriceModifier,
        $fullReindexAction = false,
        $connectionName = 'indexer',
        ?ScopeConfigInterface $scopeConfig = null,
        ?BaseSelectProcessorInterface $baseSelectProcessor = null,
        ?OptionsIndexerInterface $optionsIndexer = null,
        ?CustomOptionPriceModifier $customOptionPriceModifier = null
    ) {
        $this->baseFinalPrice = $baseFinalPrice;
        $this->indexTableStructureFactory = $indexTableStructureFactory;
        $this->tableMaintainer = $tableMaintainer;
        $this->connectionName = $connectionName;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->fullReindexAction = $fullReindexAction;
        $this->basePriceModifier = $basePriceModifier;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->baseSelectProcessor = $baseSelectProcessor ?:
            ObjectManager::getInstance()->get(BaseSelectProcessorInterface::class);
        $this->optionsIndexer = $optionsIndexer
            ?: ObjectManager::getInstance()->get(OptionsIndexerInterface::class);
        $this->customOptionPriceModifier = $customOptionPriceModifier
            ?: ObjectManager::getInstance()->get(CustomOptionPriceModifier::class);
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function executeByDimensions(array $dimensions, \Traversable $entityIds)
    {
        $this->tableMaintainer->createMainTmpTable($dimensions);

        $temporaryPriceTable = $this->indexTableStructureFactory->create([
            'tableName' => $this->tableMaintainer->getMainTmpTable($dimensions),
            'entityField' => 'entity_id',
            'customerGroupField' => 'customer_group_id',
            'websiteField' => 'website_id',
            'taxClassField' => 'tax_class_id',
            'originalPriceField' => 'price',
            'finalPriceField' => 'final_price',
            'minPriceField' => 'min_price',
            'maxPriceField' => 'max_price',
            'tierPriceField' => 'tier_price',
        ]);
        $select = $this->baseFinalPrice->getQuery(
            $dimensions,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
            iterator_to_array($entityIds)
        );
        $this->tableMaintainer->insertFromSelect(
            $select,
            $temporaryPriceTable->getTableName(),
            [
                "entity_id",
                "customer_group_id",
                "website_id",
                "tax_class_id",
                "price",
                "final_price",
                "min_price",
                "max_price",
                "tier_price",
            ]
        );

        $customOptionPriceTable = $this->createCustomOptionPriceTable($temporaryPriceTable);
        $this->basePriceModifier->modifyPrice($temporaryPriceTable, iterator_to_array($entityIds));
        $this->applyConfigurableOption(
            $temporaryPriceTable,
            $customOptionPriceTable,
            $dimensions,
            iterator_to_array($entityIds)
        );
        $this->getConnection()->dropTemporaryTable($customOptionPriceTable);
    }

    /**
     * Calculate the parent's custom option price range on its own, independent of its own price modifiers
     *
     * The parent row's min_price and max_price are later replaced by the children's range, so only the custom
     * option part may be carried over. Deriving it from the modified row is not possible: the catalog rule
     * modifier lowers min_price and final_price but not max_price, after custom options were already added.
     *
     * @param IndexTableStructure $temporaryPriceTable
     * @return string
     * @throws \Exception
     */
    private function createCustomOptionPriceTable(IndexTableStructure $temporaryPriceTable): string
    {
        $tableName = 'catalog_product_index_price_cfg_custom_opt_temp';
        $connection = $this->getConnection();
        $connection->createTemporaryTableLike(
            $tableName,
            $this->getTable('catalog_product_index_price_tmp'),
            true
        );
        $select = $connection->select()->from(
            $temporaryPriceTable->getTableName(),
            [
                'entity_id',
                'customer_group_id',
                'website_id',
                'tax_class_id',
                'price',
                'final_price',
                'min_price' => new \Zend_Db_Expr('0'),
                'max_price' => new \Zend_Db_Expr('0'),
                'tier_price',
            ]
        );
        $connection->query(
            $connection->insertFromSelect(
                $select,
                $tableName,
                [
                    'entity_id',
                    'customer_group_id',
                    'website_id',
                    'tax_class_id',
                    'price',
                    'final_price',
                    'min_price',
                    'max_price',
                    'tier_price',
                ]
            )
        );

        $this->customOptionPriceModifier->modifyPrice(
            $this->indexTableStructureFactory->create([
                'tableName' => $tableName,
                'entityField' => 'entity_id',
                'customerGroupField' => 'customer_group_id',
                'websiteField' => 'website_id',
                'taxClassField' => 'tax_class_id',
                'originalPriceField' => 'price',
                'finalPriceField' => 'final_price',
                'minPriceField' => 'min_price',
                'maxPriceField' => 'max_price',
                'tierPriceField' => 'tier_price',
            ])
        );

        return $tableName;
    }

    /**
     * Apply configurable option
     *
     * @param IndexTableStructure $temporaryPriceTable
     * @param string $customOptionPriceTableName
     * @param array $dimensions
     * @param array $entityIds
     *
     * @return $this
     * @throws \Exception
     */
    private function applyConfigurableOption(
        IndexTableStructure $temporaryPriceTable,
        string $customOptionPriceTableName,
        array $dimensions,
        array $entityIds
    ) {
        $temporaryOptionsTableName = 'catalog_product_index_price_cfg_opt_temp';
        $this->getConnection()->createTemporaryTableLike(
            $temporaryOptionsTableName,
            $this->getTable('catalog_product_index_price_cfg_opt_tmp'),
            true
        );

        $indexTableName = $this->getMainTable($dimensions);
        $this->optionsIndexer->execute($indexTableName, $temporaryOptionsTableName, $entityIds);
        $this->updateTemporaryTable(
            $temporaryPriceTable->getTableName(),
            $temporaryOptionsTableName,
            $customOptionPriceTableName
        );

        $this->getConnection()->delete($temporaryOptionsTableName);

        return $this;
    }

    /**
     * Update data in the catalog product price indexer temp table
     *
     * @param string $temporaryPriceTableName
     * @param string $temporaryOptionsTableName
     * @param string $customOptionPriceTableName
     *
     * @return void
     */
    private function updateTemporaryTable(
        string $temporaryPriceTableName,
        string $temporaryOptionsTableName,
        string $customOptionPriceTableName
    ) {
        $table = ['i' => $temporaryPriceTableName];
        $selectForCrossUpdate = $this->getConnection()->select()->join(
            ['io' => $temporaryOptionsTableName],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            []
        )->join(
            ['ico' => $customOptionPriceTableName],
            'i.entity_id = ico.entity_id AND i.customer_group_id = ico.customer_group_id' .
            ' AND i.website_id = ico.website_id',
            []
        );
        $selectForCrossUpdate->columns(
            [
                'min_price' => new \Zend_Db_Expr('ico.min_price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('ico.max_price + io.max_price'),
                'tier_price' => 'io.tier_price',
            ]
        );

        $query = $selectForCrossUpdate->crossUpdateFromSelect($table);
        $this->getConnection()->query($query);
    }

    /**
     * Get main table
     *
     * @param array $dimensions
     * @return string
     */
    private function getMainTable($dimensions)
    {
        if ($this->fullReindexAction) {
            return $this->tableMaintainer->getMainReplicaTable($dimensions);
        }
        return $this->tableMaintainer->getMainTableByDimensions($dimensions);
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     */
    private function getConnection(): \Magento\Framework\DB\Adapter\AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resource->getConnection($this->connectionName);
        }

        return $this->connection;
    }

    /**
     * Get table
     *
     * @param string $tableName
     * @return string
     */
    private function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }
}
