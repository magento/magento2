<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

/**
 * Abstract action reindex class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractAction
{
    /**
     * Default Product Type Price indexer resource model
     *
     * @var DefaultPrice
     */
    protected $_defaultIndexerResource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * Core config model
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Currency factory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_catalogProductType;

    /**
     * Indexer price factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory
     */
    protected $_indexerPriceFactory;

    /**
     * @var array|null
     */
    protected $_indexers;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice
     */
    private $tierPriceIndexResource;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory
     */
    private $dimensionCollectionFactory;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory $indexerPriceFactory
     * @param DefaultPrice $defaultIndexerResource
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice|null $tierPriceIndexResource
     * @param DimensionCollectionFactory|null $dimensionCollectionFactory
     * @param TableMaintainer|null $tableMaintainer
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
        DefaultPrice $defaultIndexerResource,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice $tierPriceIndexResource = null,
        \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory $dimensionCollectionFactory = null,
        \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer $tableMaintainer = null
    ) {
        $this->_config = $config;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
        $this->_localeDate = $localeDate;
        $this->_dateTime = $dateTime;
        $this->_catalogProductType = $catalogProductType;
        $this->_indexerPriceFactory = $indexerPriceFactory;
        $this->_defaultIndexerResource = $defaultIndexerResource;
        $this->_connection = $this->_defaultIndexerResource->getConnection();
        $this->tierPriceIndexResource = $tierPriceIndexResource ?? ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice::class
        );
        $this->dimensionCollectionFactory = $dimensionCollectionFactory ?? ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory::class
        );
        $this->tableMaintainer = $tableMaintainer ?? ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer::class
        );
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return void
     */
    abstract public function execute($ids);

    /**
     * Synchronize data between index storage and original storage
     *
     * @param array $processIds
     * @return \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated Used only for backward compatibility for indexer, which not support indexation by dimensions
     */
    protected function _syncData(array $processIds = [])
    {
        // for backward compatibility split data from old idx table on dimension tables
        foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
            $insertSelect = $this->getConnection()->select()->from(
                ['ip_tmp' => $this->_defaultIndexerResource->getIdxTable()]
            );

            foreach ($dimensions as $dimension) {
                if ($dimension->getName() === WebsiteDimensionProvider::DIMENSION_NAME) {
                    $insertSelect->where('ip_tmp.website_id = ?', $dimension->getValue());
                }
                if ($dimension->getName() === CustomerGroupDimensionProvider::DIMENSION_NAME) {
                    $insertSelect->where('ip_tmp.customer_group_id = ?', $dimension->getValue());
                }
            }

            $query = $insertSelect->insertFromSelect($this->tableMaintainer->getMainTable($dimensions));
            $this->getConnection()->query($query);
        }
        return $this;
    }

    /**
     * Prepare website current dates table
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareWebsiteDateTable()
    {
        $baseCurrency = $this->_config->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE);

        $select = $this->getConnection()->select()->from(
            ['cw' => $this->_defaultIndexerResource->getTable('store_website')],
            ['website_id']
        )->join(
            ['csg' => $this->_defaultIndexerResource->getTable('store_group')],
            'cw.default_group_id = csg.group_id',
            ['store_id' => 'default_store_id']
        )->where(
            'cw.website_id != 0'
        );

        $data = [];
        foreach ($this->getConnection()->fetchAll($select) as $item) {
            /** @var $website \Magento\Store\Model\Website */
            $website = $this->_storeManager->getWebsite($item['website_id']);

            if ($website->getBaseCurrencyCode() != $baseCurrency) {
                $rate = $this->_currencyFactory->create()->load(
                    $baseCurrency
                )->getRate(
                    $website->getBaseCurrencyCode()
                );
                if (!$rate) {
                    $rate = 1;
                }
            } else {
                $rate = 1;
            }

            /** @var $store \Magento\Store\Model\Store */
            $store = $this->_storeManager->getStore($item['store_id']);
            if ($store) {
                $timestamp = $this->_localeDate->scopeTimeStamp($store);
                $data[] = [
                    'website_id' => $website->getId(),
                    'website_date' => $this->_dateTime->formatDate($timestamp, false),
                    'rate' => $rate,
                    'default_store_id' => $store->getId()
                ];
            }
        }

        $table = $this->_defaultIndexerResource->getTable('catalog_product_index_website');
        $this->_emptyTable($table);
        if ($data) {
            foreach ($data as $row) {
                $this->getConnection()->insertOnDuplicate($table, $row, array_keys($row));
            }
        }

        return $this;
    }

    /**
     * Prepare tier price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * @return \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        $this->tierPriceIndexResource->reindexEntity((array) $entityIds);

        return $this;
    }

    /**
     * Retrieve price indexers per product type
     *
     * @param bool $fullReindexAction
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceInterface[]
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTypeIndexers($fullReindexAction = false)
    {
        if ($this->_indexers === null) {
            $this->_indexers = [];
            $types = $this->_catalogProductType->getTypesByPriority();
            foreach ($types as $typeId => $typeInfo) {
                $modelName = isset(
                    $typeInfo['price_indexer']
                ) ? $typeInfo['price_indexer'] : get_class($this->_defaultIndexerResource);

                $indexer = $this->_indexerPriceFactory->create(
                    $modelName,
                    [
                        'fullReindexAction' => $fullReindexAction
                    ]
                );
                // left setters for backward compatibility
                if ($indexer instanceof DefaultPrice) {
                    $indexer->setTypeId(
                        $typeId
                    )->setIsComposite(
                        !empty($typeInfo['composite'])
                    );
                }
                $this->_indexers[$typeId] = $indexer;
            }
        }

        return $this->_indexers;
    }

    /**
     * Retrieve Price indexer by Product Type
     *
     * @param string $productTypeId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceInterface
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getIndexer($productTypeId)
    {
        $this->getTypeIndexers();
        if (!isset($this->_indexers[$productTypeId])) {
            throw new \Magento\Framework\Exception\InputException(__('Unsupported product type "%1".', $productTypeId));
        }
        return $this->_indexers[$productTypeId];
    }

    /**
     * Copy data from source table to destination
     *
     * @param string $sourceTable
     * @param string $destTable
     * @param null|string $where
     * @return void
     */
    protected function _insertFromTable($sourceTable, $destTable, $where = null)
    {
        $sourceColumns = array_keys($this->getConnection()->describeTable($sourceTable));
        $targetColumns = array_keys($this->getConnection()->describeTable($destTable));
        $select = $this->getConnection()->select()->from($sourceTable, $sourceColumns);
        if ($where) {
            $select->where($where);
        }
        $query = $this->getConnection()->insertFromSelect(
            $select,
            $destTable,
            $targetColumns,
            \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
        );
        $this->getConnection()->query($query);
    }

    /**
     * Removes all data from the table
     *
     * @param string $table
     * @return void
     */
    protected function _emptyTable($table)
    {
        $this->getConnection()->delete($table);
    }

    /**
     * Refresh entities index
     *
     * @param array $changedIds
     * @return array Affected ids
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _reindexRows($changedIds = [])
    {
        $this->_prepareWebsiteDateTable();

        $productsTypes = $this->getProductsTypes($changedIds);
        $parentProductsTypes = $this->getParentProductsTypes($changedIds);

        $changedIds = array_merge($changedIds, ...array_values($parentProductsTypes));
        $productsTypes = array_merge_recursive($productsTypes, $parentProductsTypes);

        if ($changedIds) {
            $this->deleteIndexData($changedIds);
        }
        foreach ($productsTypes as $productType => $entityIds) {
            $indexer = $this->_getIndexer($productType);
            if ($indexer instanceof DimensionalIndexerInterface) {
                foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
                    $this->tableMaintainer->createMainTmpTable($dimensions);
                    $temporaryTable = $this->tableMaintainer->getMainTmpTable($dimensions);
                    $this->_emptyTable($temporaryTable);
                    $indexer->executeByDimensions($dimensions, \SplFixedArray::fromArray($entityIds, false));
                    // copy to index
                    $this->_insertFromTable(
                        $temporaryTable,
                        $this->tableMaintainer->getMainTable($dimensions)
                    );
                }
            } else {
                // handle 3d-party indexers for backward compatibility
                $this->_emptyTable($this->_defaultIndexerResource->getIdxTable());
                $this->_copyRelationIndexData($entityIds);
                $indexer->reindexEntity($entityIds);
                $this->_syncData($entityIds);
            }
        }

        return $changedIds;
    }

    /**
     * @param array $entityIds
     * @return void
     */
    private function deleteIndexData(array $entityIds)
    {
        foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
            $select = $this->getConnection()->select()->from(
                ['index_price' => $this->tableMaintainer->getMainTable($dimensions)],
                null
            )->where('index_price.entity_id IN (?)', $entityIds);
            $query = $select->deleteFromSelect('index_price');
            $this->getConnection()->query($query);
        }
    }

    /**
     * Copy relations product index from primary index to temporary index table by parent entity
     *
     * @param null|array $parentIds
     * @param array $excludeIds
     * @return \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
     * @deprecated Used only for backward compatibility for do not broke custom indexer implementation
     * which do not work by dimensions.
     * For indexers, which support dimensions all composite products read data directly from main price indexer table
     * or replica table for partial or full reindex correspondingly.
     */
    protected function _copyRelationIndexData($parentIds, $excludeIds = null)
    {
        $linkField = $this->getProductIdFieldName();
        $select = $this->getConnection()->select()->from(
            $this->_defaultIndexerResource->getTable('catalog_product_relation'),
            ['child_id']
        )->join(
            ['e' => $this->_defaultIndexerResource->getTable('catalog_product_entity')],
            'e.' . $linkField . ' = parent_id',
            []
        )->where(
            'e.entity_id IN(?)',
            $parentIds
        );
        if (!empty($excludeIds)) {
            $select->where('child_id NOT IN(?)', $excludeIds);
        }

        $children = $this->getConnection()->fetchCol($select);

        if ($children) {
            foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
                $select = $this->getConnection()->select()->from(
                    $this->getIndexTargetTableByDimension($dimensions)
                )->where(
                    'entity_id IN(?)',
                    $children
                );
                $query = $select->insertFromSelect($this->_defaultIndexerResource->getIdxTable(), [], false);
                $this->getConnection()->query($query);
            }
        }

        return $this;
    }

    /**
     * Retrieve index table by dimension that will be used for write operations.
     *
     * This method is used during both partial and full reindex to identify the table.
     *
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     *
     * @return string
     */
    private function getIndexTargetTableByDimension(array $dimensions)
    {
        $indexTargetTable = $this->getIndexTargetTable();
        if ($indexTargetTable === self::getIndexTargetTable()) {
            $indexTargetTable = $this->tableMaintainer->getMainTable($dimensions);
        }
        if ($indexTargetTable === self::getIndexTargetTable() . '_replica') {
            $indexTargetTable = $this->tableMaintainer->getMainReplicaTable($dimensions);
        }
        return $indexTargetTable;
    }

    /**
     * Retrieve index table that will be used for write operations.
     *
     * This method is used during both partial and full reindex to identify the table.
     *
     * @return string
     */
    protected function getIndexTargetTable()
    {
        return $this->_defaultIndexerResource->getTable('catalog_product_index_price');
    }

    /**
     * @return string
     */
    protected function getProductIdFieldName()
    {
        $table = $this->_defaultIndexerResource->getTable('catalog_product_entity');
        $indexList = $this->getConnection()->getIndexList($table);
        return $indexList[$this->getConnection()->getPrimaryKeyName($table)]['COLUMNS_LIST'][0];
    }

    /**
     * Get products types.
     *
     * @param array $changedIds
     * @return array
     */
    private function getProductsTypes(array $changedIds = [])
    {
        $select = $this->getConnection()->select()->from(
            $this->_defaultIndexerResource->getTable('catalog_product_entity'),
            ['entity_id', 'type_id']
        );
        if ($changedIds) {
            $select->where('entity_id IN (?)', $changedIds);
        }
        $pairs = $this->getConnection()->fetchPairs($select);

        $byType = [];
        foreach ($pairs as $productId => $productType) {
            $byType[$productType][$productId] = $productId;
        }

        return $byType;
    }

    /**
     * Get parent products types
     * Used for add composite products to reindex if we have only simple products in changed ids set
     *
     * @param array $productsIds
     * @return array
     */
    private function getParentProductsTypes(array $productsIds)
    {
        $select = $this->getConnection()->select()->from(
            ['l' => $this->_defaultIndexerResource->getTable('catalog_product_relation')],
            ''
        )->join(
            ['e' => $this->_defaultIndexerResource->getTable('catalog_product_entity')],
            'e.' . $this->getProductIdFieldName() . ' = l.parent_id',
            ['e.entity_id as parent_id', 'type_id']
        )->where(
            'l.child_id IN(?)',
            $productsIds
        );
        $pairs = $this->getConnection()->fetchPairs($select);

        $byType = [];
        foreach ($pairs as $productId => $productType) {
            $byType[$productType][$productId] = $productId;
        }

        return $byType;
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->_defaultIndexerResource->getConnection();
    }
}
