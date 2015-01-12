<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

/**
 * Abstract action reindex class
 *
 */
abstract class AbstractAction
{
    /**
     * Default Product Type Price indexer resource model
     *
     * @var string
     */
    protected $_defaultPriceIndexer;

    /**
     * Resource instance
     *
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

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
     * @var \Magento\Catalog\Model\Resource\Product\Indexer\Price\Factory
     */
    protected $_indexerPriceFactory;

    /**
     * @var array|null
     */
    protected $_indexers;

    /**
     * Flag that defines if need to use "_idx" index table suffix instead of "_tmp"
     *
     * @var bool
     */
    protected $_useIdxTable = false;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Catalog\Model\Resource\Product\Indexer\Price\Factory $indexerPriceFactory
     * @param string $defaultPriceIndexer
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Catalog\Model\Resource\Product\Indexer\Price\Factory $indexerPriceFactory,
        $defaultPriceIndexer
    ) {
        $this->_resource = $resource;
        $this->_config = $config;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
        $this->_localeDate = $localeDate;
        $this->_dateTime = $dateTime;
        $this->_catalogProductType = $catalogProductType;
        $this->_indexerPriceFactory = $indexerPriceFactory;
        $this->_defaultPriceIndexer = $defaultPriceIndexer;
    }

    /**
     * Retrieve connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resource->getConnection('write');
        }
        return $this->_connection;
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
        $select = $this->_getConnection()->select()->from(
            ['index_price' => $this->_getTable('catalog_product_index_price')],
            null
        )->joinLeft(
            ['ip_tmp' => $this->_getIdxTable()],
            'index_price.entity_id = ip_tmp.entity_id AND index_price.website_id = ip_tmp.website_id',
            []
        )->where(
            'ip_tmp.entity_id IS NULL'
        );
        if (!empty($processIds)) {
            $select->where('index_price.entity_id IN(?)', $processIds);
        }
        $sql = $select->deleteFromSelect('index_price');
        $this->_getConnection()->query($sql);

        $this->_insertFromTable($this->_getIdxTable(), $this->_getTable('catalog_product_index_price'));
        return $this;
    }

    /**
     * Returns table name for given entity
     *
     * @param string $entityName
     * @return string
     */
    protected function _getTable($entityName)
    {
        return $this->_resource->getTableName($entityName);
    }

    /**
     * Prepare website current dates table
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
     */
    protected function _prepareWebsiteDateTable()
    {
        $write = $this->_getConnection();
        $baseCurrency = $this->_config->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE);

        $select = $write->select()->from(
            ['cw' => $this->_getTable('store_website')],
            ['website_id']
        )->join(
            ['csg' => $this->_getTable('store_group')],
            'cw.default_group_id = csg.group_id',
            ['store_id' => 'default_store_id']
        )->where(
            'cw.website_id != 0'
        );

        $data = [];
        foreach ($write->fetchAll($select) as $item) {
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

        $table = $this->_getTable('catalog_product_index_website');
        $this->_emptyTable($table);
        if ($data) {
            $write->insertMultiple($table, $data);
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
        $write = $this->_getConnection();
        $table = $this->_getTable('catalog_product_index_tier_price');
        $this->_emptyTable($table);

        $websiteExpression = $write->getCheckSql('tp.website_id = 0', 'ROUND(tp.value * cwd.rate, 4)', 'tp.value');
        $select = $write->select()->from(
            ['tp' => $this->_getTable(['catalog_product_entity', 'tier_price'])],
            ['entity_id']
        )->join(
            ['cg' => $this->_getTable('customer_group')],
            'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)',
            ['customer_group_id']
        )->join(
            ['cw' => $this->_getTable('store_website')],
            'tp.website_id = 0 OR tp.website_id = cw.website_id',
            ['website_id']
        )->join(
            ['cwd' => $this->_getTable('catalog_product_index_website')],
            'cw.website_id = cwd.website_id',
            []
        )->where(
            'cw.website_id != 0'
        )->columns(
            new \Zend_Db_Expr("MIN({$websiteExpression})")
        )->group(
            ['tp.entity_id', 'cg.customer_group_id', 'cw.website_id']
        );

        if (!empty($entityIds)) {
            $select->where('tp.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($table);
        $write->query($query);

        return $this;
    }

    /**
     * Prepare group price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * @return \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
     */
    protected function _prepareGroupPriceIndex($entityIds = null)
    {
        $write = $this->_getConnection();
        $table = $this->_getTable('catalog_product_index_group_price');
        $this->_emptyTable($table);

        $websiteExpression = $write->getCheckSql('gp.website_id = 0', 'ROUND(gp.value * cwd.rate, 4)', 'gp.value');
        $select = $write->select()->from(
            ['gp' => $this->_getTable(['catalog_product_entity', 'group_price'])],
            ['entity_id']
        )->join(
            ['cg' => $this->_getTable('customer_group')],
            'gp.all_groups = 1 OR (gp.all_groups = 0 AND gp.customer_group_id = cg.customer_group_id)',
            ['customer_group_id']
        )->join(
            ['cw' => $this->_getTable('store_website')],
            'gp.website_id = 0 OR gp.website_id = cw.website_id',
            ['website_id']
        )->join(
            ['cwd' => $this->_getTable('catalog_product_index_website')],
            'cw.website_id = cwd.website_id',
            []
        )->where(
            'cw.website_id != 0'
        )->columns(
            new \Zend_Db_Expr("MIN({$websiteExpression})")
        )->group(
            ['gp.entity_id', 'cg.customer_group_id', 'cw.website_id']
        );

        if (!empty($entityIds)) {
            $select->where('gp.entity_id IN(?)', $entityIds);
        }

        $query = $select->insertFromSelect($table);
        $write->query($query);

        return $this;
    }

    /**
     * Retrieve price indexers per product type
     *
     * @return \Magento\Catalog\Model\Resource\Product\Indexer\Price\PriceInterface[]
     */
    public function getTypeIndexers()
    {
        if (is_null($this->_indexers)) {
            $this->_indexers = [];
            $types = $this->_catalogProductType->getTypesByPriority();
            foreach ($types as $typeId => $typeInfo) {
                $modelName = isset(
                    $typeInfo['price_indexer']
                ) ? $typeInfo['price_indexer'] : $this->_defaultPriceIndexer;

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
     * @return \Magento\Catalog\Model\Resource\Product\Indexer\Price\PriceInterface
     * @throws \Magento\Catalog\Exception
     */
    protected function _getIndexer($productTypeId)
    {
        $this->getTypeIndexers();
        if (!isset($this->_indexers[$productTypeId])) {
            throw new \Magento\Catalog\Exception(__('Unsupported product type "%1".', $productTypeId));
        }
        return $this->_indexers[$productTypeId];
    }

    /**
     * Copy data from source table of read adapter to destination table of index adapter
     *
     * @param string $sourceTable
     * @param string $destTable
     * @param null|string $where
     * @return void
     */
    protected function _insertFromTable($sourceTable, $destTable, $where = null)
    {
        $connection = $this->_getConnection();
        $sourceColumns = array_keys($connection->describeTable($sourceTable));
        $targetColumns = array_keys($connection->describeTable($destTable));
        $select = $connection->select()->from($sourceTable, $sourceColumns);
        if ($where) {
            $select->where($where);
        }
        $query = $connection->insertFromSelect(
            $select,
            $destTable,
            $targetColumns,
            \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
        );
        $connection->query($query);
    }

    /**
     * Set or get what either "_idx" or "_tmp" suffixed temporary index table need to use
     *
     * @param bool $value
     * @return bool
     */
    protected function _useIdxTable($value = null)
    {
        if (!is_null($value)) {
            $this->_useIdxTable = (bool)$value;
        }
        return $this->_useIdxTable;
    }

    /**
     * Retrieve temporary index table name
     *
     * @return string
     */
    protected function _getIdxTable()
    {
        if ($this->_useIdxTable()) {
            return $this->_getTable('catalog_product_index_price_idx');
        }
        return $this->_getTable('catalog_product_index_price_tmp');
    }

    /**
     * Removes all data from the table
     *
     * @param string $table
     * @return void
     */
    protected function _emptyTable($table)
    {
        $this->_getConnection()->delete($table);
    }

    /**
     * Refresh entities index
     *
     * @param array $changedIds
     * @return array Affected ids
     */
    protected function _reindexRows($changedIds = [])
    {
        $this->_emptyTable($this->_getIdxTable());
        $this->_prepareWebsiteDateTable();

        $select = $this->_connection->select()->from(
            $this->_getTable('catalog_product_entity'),
            ['entity_id', 'type_id']
        )->where(
            'entity_id IN(?)',
            $changedIds
        );
        $pairs = $this->_connection->fetchPairs($select);
        $byType = [];
        foreach ($pairs as $productId => $productType) {
            $byType[$productType][$productId] = $productId;
        }

        $compositeIds = [];
        $notCompositeIds = [];

        foreach ($byType as $productType => $entityIds) {
            $indexer = $this->_getIndexer($productType);
            if ($indexer->getIsComposite()) {
                $compositeIds += $entityIds;
            } else {
                $notCompositeIds += $entityIds;
            }
        }

        if (!empty($notCompositeIds)) {
            $select = $this->_connection->select()->from(
                ['l' => $this->_getTable('catalog_product_relation')],
                'parent_id'
            )->join(
                ['e' => $this->_getTable('catalog_product_entity')],
                'e.entity_id = l.parent_id',
                ['type_id']
            )->where(
                'l.child_id IN(?)',
                $notCompositeIds
            );
            $pairs = $this->_connection->fetchPairs($select);
            foreach ($pairs as $productId => $productType) {
                if (!in_array($productId, $changedIds)) {
                    $changedIds[] = $productId;
                    $byType[$productType][$productId] = $productId;
                    $compositeIds[$productId] = $productId;
                }
            }
        }

        if (!empty($compositeIds)) {
            $this->_copyRelationIndexData($compositeIds, $notCompositeIds);
        }
        $this->_prepareTierPriceIndex($compositeIds + $notCompositeIds);
        $this->_prepareGroupPriceIndex($compositeIds + $notCompositeIds);

        $indexers = $this->getTypeIndexers();
        foreach ($indexers as $indexer) {
            if (!empty($byType[$indexer->getTypeId()])) {
                $indexer->reindexEntity($byType[$indexer->getTypeId()]);
            }
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
        $write = $this->_connection;
        $select = $write->select()->from(
            $this->_getTable('catalog_product_relation'),
            ['child_id']
        )->where(
            'parent_id IN(?)',
            $parentIds
        );
        if (!empty($excludeIds)) {
            $select->where('child_id NOT IN(?)', $excludeIds);
        }

        $children = $write->fetchCol($select);

        if ($children) {
            $select = $write->select()->from(
                $this->_getTable('catalog_product_index_price')
            )->where(
                'entity_id IN(?)',
                $children
            );
            $query = $select->insertFromSelect($this->_getIdxTable(), [], false);
            $write->query($query);
        }

        return $this;
    }
}
