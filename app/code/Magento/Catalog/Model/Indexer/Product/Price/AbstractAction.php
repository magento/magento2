<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price;

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
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory $indexerPriceFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice $defaultIndexerResource
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory $indexerPriceFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice $defaultIndexerResource
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
        $table = $this->_defaultIndexerResource->getTable('catalog_product_index_tier_price');
        $this->_emptyTable($table);
        if (empty($entityIds)) {
            return $this;
        }
        $linkField = $this->getProductIdFieldName();
        $priceAttribute = $this->getProductResource()->getAttribute('price');
        $baseColumns = [
            'cpe.entity_id',
            'tp.customer_group_id',
            'tp.website_id'
        ];
        if ($linkField !== 'entity_id') {
            $baseColumns[] = 'cpe.' . $linkField;
        };
        $subSelect = $this->_connection->select()->from(
            ['cpe' => $this->_defaultIndexerResource->getTable('catalog_product_entity')],
            array_merge_recursive(
                $baseColumns,
                [
                    'min(tp.value) AS value',
                    'min(tp.percentage_value) AS percentage_value'
                ]
            )
        )->joinInner(
            ['tp' => $this->_defaultIndexerResource->getTable(['catalog_product_entity', 'tier_price'])],
            'tp.' . $linkField . ' = cpe.' . $linkField,
            []
        )->where("cpe.entity_id IN(?)", $entityIds)
            ->where("tp.website_id != 0")
            ->group(['cpe.entity_id', 'tp.customer_group_id', 'tp.website_id']);

        $subSelect2 = $this->_connection->select()
            ->from(
                ['cpe' => $this->_defaultIndexerResource->getTable('catalog_product_entity')],
                array_merge_recursive(
                    $baseColumns,
                    [
                        'MIN(ROUND(tp.value * cwd.rate, 4)) AS value',
                        'MIN(ROUND(tp.percentage_value * cwd.rate, 4)) AS percentage_value'

                    ]
                )
            )
            ->joinInner(
                ['tp' => $this->_defaultIndexerResource->getTable(['catalog_product_entity', 'tier_price'])],
                'tp.' . $linkField . ' = cpe.' . $linkField,
                []
            )->join(
                ['cw' => $this->_defaultIndexerResource->getTable('store_website')],
                true,
                []
            )
            ->joinInner(
                ['cwd' => $this->_defaultIndexerResource->getTable('catalog_product_index_website')],
                'cw.website_id = cwd.website_id',
                []
            )
            ->where("cpe.entity_id IN(?)", $entityIds)
            ->where("tp.website_id = 0")
            ->group(
                ['cpe.entity_id', 'tp.customer_group_id', 'tp.website_id']
            );

        $unionSelect = $this->_connection->select()
            ->union([$subSelect, $subSelect2], \Magento\Framework\DB\Select::SQL_UNION_ALL);
        $select = $this->_connection->select()
            ->from(
                ['b' => new \Zend_Db_Expr(sprintf('(%s)', $unionSelect->assemble()))],
                [
                    'b.entity_id',
                    'b.customer_group_id',
                    'b.website_id',
                    'MIN(IF(b.value = 0, product_price.value * (1 - b.percentage_value / 100), b.value))'
                ]
            )
            ->joinInner(
                ['product_price' => $priceAttribute->getBackend()->getTable()],
                'b.' . $linkField . ' = product_price.' . $linkField,
                []
            )
            ->group(['b.entity_id', 'b.customer_group_id', 'b.website_id']);

        $query = $select->insertFromSelect($table, [], false);

        $this->_connection->query($query);
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _reindexRows($changedIds = [])
    {
        $this->_emptyTable($this->_defaultIndexerResource->getIdxTable());
        $this->_prepareWebsiteDateTable();

        $select = $this->_connection->select()->from(
            $this->_defaultIndexerResource->getTable('catalog_product_entity'),
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
                ['l' => $this->_defaultIndexerResource->getTable('catalog_product_relation')],
                ''
            )->join(
                ['e' => $this->_defaultIndexerResource->getTable('catalog_product_entity')],
                'e.' . $this->getProductIdFieldName() . ' = l.parent_id',
                ['e.entity_id as parent_id', 'type_id']
            )->where(
                'l.child_id IN(?)',
                $notCompositeIds
            );
            $pairs = $this->_connection->fetchPairs($select);
            foreach ($pairs as $productId => $productType) {
                if (!in_array($productId, $changedIds)) {
                    $changedIds[] = (string) $productId;
                    $byType[$productType][$productId] = $productId;
                    $compositeIds[$productId] = $productId;
                }
            }
        }

        if (!empty($compositeIds)) {
            $this->_copyRelationIndexData($compositeIds, $notCompositeIds);
        }
        $this->_prepareTierPriceIndex($compositeIds + $notCompositeIds);

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
        $linkField = $this->getProductIdFieldName();
        $select = $this->_connection->select()->from(
            $this->_defaultIndexerResource->getTable('catalog_product_relation'),
            ['child_id']
        )->join(
            ['e' => $this->_defaultIndexerResource->getTable('catalog_product_entity')],
            'e.' . $linkField . ' = parent_id'
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
     * @return \Magento\Catalog\Model\ResourceModel\Product
     * @deprecated
     */
    private function getProductResource()
    {
        if (null === $this->productResource) {
            $this->productResource = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        }
        return $this->productResource;
    }
}
