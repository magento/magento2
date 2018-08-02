<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\App\ObjectManager;

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
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
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
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory $indexerPriceFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice $defaultIndexerResource
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice $tierPriceIndexResource
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
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice $tierPriceIndexResource = null
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
        $this->tierPriceIndexResource = $tierPriceIndexResource ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice::class
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
     */
    protected function _syncData(array $processIds = [])
    {
        // delete invalid rows
        $select = $this->_connection->select()->from(
            ['index_price' => $this->getIndexTargetTable()],
            null
        )->joinLeft(
            ['ip_tmp' => $this->_defaultIndexerResource->getIdxTable()],
            'index_price.entity_id = ip_tmp.entity_id AND index_price.website_id = ip_tmp.website_id',
            []
        )->where(
            'ip_tmp.entity_id IS NULL'
        );
        if (!empty($processIds)) {
            $select->where('index_price.entity_id IN(?)', $processIds);
        }
        $sql = $select->deleteFromSelect('index_price');
        $this->_connection->query($sql);

        $this->_insertFromTable(
            $this->_defaultIndexerResource->getIdxTable(),
            $this->getIndexTargetTable()
        );
        return $this;
    }

    /**
     * Prepare website current dates table
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
     */
    protected function _prepareWebsiteDateTable()
    {
        $baseCurrency = $this->_config->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE);

        $select = $this->_connection->select()->from(
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
        foreach ($this->_connection->fetchAll($select) as $item) {
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
                ];
            }
        }

        $table = $this->_defaultIndexerResource->getTable('catalog_product_index_website');
        $this->_emptyTable($table);
        if ($data) {
            foreach ($data as $row) {
                $this->_connection->insertOnDuplicate($table, $row, array_keys($row));
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
     * @return \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceInterface[]
     */
    public function getTypeIndexers()
    {
        if ($this->_indexers === null) {
            $this->_indexers = [];
            $types = $this->_catalogProductType->getTypesByPriority();
            foreach ($types as $typeId => $typeInfo) {
                $modelName = isset(
                    $typeInfo['price_indexer']
                ) ? $typeInfo['price_indexer'] : get_class($this->_defaultIndexerResource);

                $isComposite = !empty($typeInfo['composite']);
                $indexer = $this->_indexerPriceFactory->create(
                    $modelName
                )->setTypeId(
                    $typeId
                )->setIsComposite(
                    $isComposite
                );
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
     * @throws \Magento\Framework\Exception\InputException
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
        $sourceColumns = array_keys($this->_connection->describeTable($sourceTable));
        $targetColumns = array_keys($this->_connection->describeTable($destTable));
        $select = $this->_connection->select()->from($sourceTable, $sourceColumns);
        if ($where) {
            $select->where($where);
        }
        $query = $this->_connection->insertFromSelect(
            $select,
            $destTable,
            $targetColumns,
            \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
        );
        $this->_connection->query($query);
    }

    /**
     * Removes all data from the table
     *
     * @param string $table
     * @return void
     */
    protected function _emptyTable($table)
    {
        $this->_connection->delete($table);
    }

    /**
     * Refresh entities index
     *
     * @param array $changedIds
     * @return array Affected ids
     */
    protected function _reindexRows($changedIds = [])
    {
        $this->_emptyTable($this->_defaultIndexerResource->getIdxTable());
        $this->_prepareWebsiteDateTable();

        $productsTypes = $this->getProductsTypes($changedIds);
        $compositeIds = [];
        $notCompositeIds = [];

        foreach ($productsTypes as $productType => $entityIds) {
            $indexer = $this->_getIndexer($productType);
            if ($indexer->getIsComposite()) {
                $compositeIds += $entityIds;
            } else {
                $notCompositeIds += $entityIds;
            }
        }

        if (!empty($notCompositeIds)) {
            $parentProductsTypes = $this->getParentProductsTypes($notCompositeIds);
            $productsTypes = array_merge_recursive($productsTypes, $parentProductsTypes);
            foreach ($parentProductsTypes as $parentProductsIds) {
                $compositeIds = $compositeIds + $parentProductsIds;
                $changedIds = array_merge($changedIds, $parentProductsIds);
            }
        }

        if (!empty($compositeIds)) {
            $this->_copyRelationIndexData($compositeIds, $notCompositeIds);
        }
        $this->_prepareTierPriceIndex($compositeIds + $notCompositeIds);

        foreach ($productsTypes as $productType => $entityIds) {
            $indexer = $this->_getIndexer($productType);
            $indexer->reindexEntity($entityIds);
        }
        $this->_syncData($changedIds);

        return $compositeIds + $notCompositeIds;
    }

    /**
     * Copy relations product index from primary index to temporary index table by parent entity
     *
     * @param null|array $parentIds
     * @param array $excludeIds
     * @return \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
     */
    protected function _copyRelationIndexData($parentIds, $excludeIds = null)
    {
        $linkField = $this->getProductIdFieldName();
        $select = $this->_connection->select()->from(
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

        $children = $this->_connection->fetchCol($select);

        if ($children) {
            $select = $this->_connection->select()->from(
                $this->getIndexTargetTable()
            )->where(
                'entity_id IN(?)',
                $children
            );
            $query = $select->insertFromSelect($this->_defaultIndexerResource->getIdxTable(), [], false);
            $this->_connection->query($query);
        }

        return $this;
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
        $indexList = $this->_connection->getIndexList($table);
        return $indexList[$this->_connection->getPrimaryKeyName($table)]['COLUMNS_LIST'][0];
    }

    /**
     * Get products types.
     *
     * @param array $changedIds
     * @return array
     */
    private function getProductsTypes(array $changedIds = [])
    {
        $select = $this->_connection->select()->from(
            $this->_defaultIndexerResource->getTable('catalog_product_entity'),
            ['entity_id', 'type_id']
        );
        if ($changedIds) {
            $select->where('entity_id IN (?)', $changedIds);
        }
        $pairs = $this->_connection->fetchPairs($select);

        $byType = [];
        foreach ($pairs as $productId => $productType) {
            $byType[$productType][$productId] = $productId;
        }

        return $byType;
    }

    /**
     * Get parent products types.
     *
     * @param array $productsIds
     * @return array
     */
    private function getParentProductsTypes(array $productsIds)
    {
        $select = $this->_connection->select()->from(
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
        $pairs = $this->_connection->fetchPairs($select);

        $byType = [];
        foreach ($pairs as $productId => $productType) {
            $byType[$productType][$productId] = $productId;
        }

        return $byType;
    }
}
