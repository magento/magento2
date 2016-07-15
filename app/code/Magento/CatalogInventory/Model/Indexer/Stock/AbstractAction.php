<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;

/**
 * Abstract action reindex class
 *
 * @package Magento\CatalogInventory\Model\Indexer\Stock
 */
abstract class AbstractAction
{
    /**
     * Resource instance
     *
     * @var Resource
     */
    protected $_resource;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Indexer\StockFactory
     */
    protected $_indexerFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_catalogProductType;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * Stock Indexer models per product type
     * Sorted by priority
     *
     * @var array
     */
    protected $_indexers = [];

    /**
     * Flag that defines if need to use "_idx" index table suffix instead of "_tmp"
     *
     * @var bool
     */
    protected $_isNeedUseIdxTable = false;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\CatalogInventory\Model\ResourceModel\Indexer\StockFactory $indexerFactory
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Indexer\CacheContext $cacheContext
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\CatalogInventory\Model\ResourceModel\Indexer\StockFactory $indexerFactory,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Indexer\CacheContext $cacheContext,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_resource = $resource;
        $this->_indexerFactory = $indexerFactory;
        $this->_catalogProductType = $catalogProductType;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     *
     * @return void
     */
    abstract public function execute($ids);

    /**
     * Retrieve connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resource->getConnection();
        }
        return $this->_connection;
    }

    /**
     * Retrieve Stock Indexer Models per Product Type
     *
     * @return \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\StockInterface[]
     */
    protected function _getTypeIndexers()
    {
        if (empty($this->_indexers)) {
            foreach ($this->_catalogProductType->getTypesByPriority() as $typeId => $typeInfo) {
                $indexerClassName = isset($typeInfo['stock_indexer']) ? $typeInfo['stock_indexer'] : '';

                $indexer = $this->_indexerFactory->create($indexerClassName)
                    ->setTypeId($typeId)
                    ->setIsComposite(!empty($typeInfo['composite']));

                $this->_indexers[$typeId] = $indexer;
            }
        }
        return $this->_indexers;
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
     * Retrieve product relations by children
     *
     * @param int|array $childIds
     * @return array
     */
    public function getRelationsByChild($childIds)
    {
        $connection = $this->_getConnection();
        $select = $connection->select()
            ->from($this->_getTable('catalog_product_relation'), 'parent_id')
            ->where('child_id IN(?)', $childIds);

        return $connection->fetchCol($select);
    }

    /**
     * Reindex all
     *
     * @return void
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        $this->clearTemporaryIndexTable();

        foreach ($this->_getTypeIndexers() as $indexer) {
            $indexer->reindexAll();
        }

        $this->_syncData();
    }

    /**
     * Synchronize data between index storage and original storage
     *
     * @return $this
     */
    protected function _syncData()
    {
        $idxTableName = $this->_getIdxTable();
        $tableName = $this->_getTable('cataloginventory_stock_status');

        $this->_deleteOldRelations($tableName);

        $columns = array_keys($this->_connection->describeTable($idxTableName));
        $select = $this->_connection->select()->from($idxTableName, $columns);
        $query = $select->insertFromSelect($tableName, $columns);
        $this->_connection->query($query);
        return $this;
    }

    /**
     * Delete old relations
     *
     * @param string $tableName
     *
     * @return void
     */
    protected function _deleteOldRelations($tableName)
    {
        $select = $this->_connection->select()
            ->from(['s' => $tableName])
            ->joinLeft(
                ['w' => $this->_getTable('catalog_product_website')],
                's.product_id = w.product_id AND s.website_id = w.website_id',
                []
            )
            ->where('w.product_id IS NULL');

        $sql = $select->deleteFromSelect('s');
        $this->_connection->query($sql);
    }

    /**
     * Refresh entities index
     *
     * @param array $productIds
     * @return array Affected ids
     */
    protected function _reindexRows($productIds = [])
    {
        $connection = $this->_getConnection();
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }
        $parentIds = $this->getRelationsByChild($productIds);
        $processIds = $parentIds ? array_merge($parentIds, $productIds) : $productIds;

        // retrieve product types by processIds
        $select = $connection->select()
            ->from($this->_getTable('catalog_product_entity'), ['entity_id', 'type_id'])
            ->where('entity_id IN(?)', $processIds);
        $pairs = $connection->fetchPairs($select);

        $byType = [];
        foreach ($pairs as $productId => $typeId) {
            $byType[$typeId][$productId] = $productId;
        }

        $indexers = $this->_getTypeIndexers();
        foreach ($indexers as $indexer) {
            if (isset($byType[$indexer->getTypeId()])) {
                $indexer->reindexEntity($byType[$indexer->getTypeId()]);
            }
        }
        
        $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        
        return $this;
    }

    /**
     * Set or get what either "_idx" or "_tmp" suffixed temporary index table need to use
     *
     * @param bool|null $value
     * @return bool
     */
    public function useIdxTable($value = null)
    {
        if ($value !== null) {
            $this->_isNeedUseIdxTable = (bool)$value;
        }
        return $this->_isNeedUseIdxTable;
    }

    /**
     * Retrieve temporary index table name
     *
     * @return string
     */
    protected function _getIdxTable()
    {
        if ($this->useIdxTable()) {
            return $this->_getTable('cataloginventory_stock_status_idx');
        }
        return $this->_getTable('cataloginventory_stock_status_tmp');
    }

    /**
     * Clean up temporary index table
     *
     * @return void
     */
    public function clearTemporaryIndexTable()
    {
        $this->_getConnection()->delete($this->_getIdxTable());
    }
}
