<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Abstract action reindex class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
abstract class AbstractAction
{
    /**
     * Suffix for value field on composite attributes
     *
     * @var string
     * @since 2.0.0
     */
    protected $_valueFieldSuffix = '_value';

    /**
     * Suffix for drop table (uses on flat table rename)
     *
     * @var string
     * @since 2.0.0
     */
    protected $_tableDropSuffix = '_drop_indexer';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     * @since 2.0.0
     */
    protected $_productIndexerHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected $_connection;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     * @since 2.0.0
     */
    protected $_productType;

    /**
     * Existing flat tables flags pool
     *
     * @var array
     * @since 2.0.0
     */
    protected $_flatTablesExist = [];

    /**
     * List of product types available in installation
     *
     * @var array
     * @since 2.0.0
     */
    protected $_productTypes = [];

    /**
     * @var TableBuilder
     * @since 2.0.0
     */
    protected $_tableBuilder;

    /**
     * @var FlatTableBuilder
     * @since 2.0.0
     */
    protected $_flatTableBuilder;

    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param TableBuilder $tableBuilder
     * @param FlatTableBuilder $flatTableBuilder
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Catalog\Model\Product\Type $productType,
        TableBuilder $tableBuilder,
        FlatTableBuilder $flatTableBuilder
    ) {
        $this->_storeManager = $storeManager;
        $this->_productIndexerHelper = $productHelper;
        $this->_productType = $productType;
        $this->_connection = $resource->getConnection();
        $this->_tableBuilder = $tableBuilder;
        $this->_flatTableBuilder = $flatTableBuilder;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     * @since 2.0.0
     */
    abstract public function execute($ids);

    /**
     * Return temporary table name by regular table name
     *
     * @param string $tableName
     * @return string
     * @since 2.0.0
     */
    protected function _getTemporaryTableName($tableName)
    {
        return sprintf('%s_tmp_indexer', $tableName);
    }

    /**
     * Drop temporary tables created by reindex process
     *
     * @param array $tablesList
     * @param int|string $storeId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    protected function _cleanOnFailure(array $tablesList, $storeId)
    {
        foreach ($tablesList as $table => $columns) {
            $this->_connection->dropTemporaryTable($table);
        }
        $tableName = $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($storeId));
        $this->_connection->dropTable($tableName);
    }

    /**
     * Rebuild catalog flat index from scratch
     *
     * @param int $storeId
     * @param array $changedIds
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    protected function _reindex($storeId, array $changedIds = [])
    {
        try {
            $this->_tableBuilder->build($storeId, $changedIds, $this->_valueFieldSuffix);
            $this->_flatTableBuilder->build(
                $storeId,
                $changedIds,
                $this->_valueFieldSuffix,
                $this->_tableDropSuffix,
                true
            );

            $this->_updateRelationProducts($storeId, $changedIds);
            $this->_cleanRelationProducts($storeId);
        } catch (\Exception $e) {
            $attributes = $this->_productIndexerHelper->getAttributes();
            $eavAttributes = $this->_productIndexerHelper->getTablesStructure($attributes);
            $this->_cleanOnFailure($eavAttributes, $storeId);
            throw $e;
        }
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getProductTypeInstances()
    {
        if ($this->_productTypes === null) {
            $this->_productTypes = [];
            $productEmulator = new \Magento\Framework\DataObject();
            foreach (array_keys($this->_productType->getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->_productTypes[$typeId] = $this->_productType->factory($productEmulator);
            }
        }
        return $this->_productTypes;
    }

    /**
     * Update relation products
     *
     * @param int $storeId
     * @param int|array $productIds Update child product(s) only
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _updateRelationProducts($storeId, $productIds = null)
    {
        if (!$this->_productIndexerHelper->isAddChildData() || !$this->_isFlatTableExists($storeId)) {
            return $this;
        }

        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);

        foreach ($this->_getProductTypeInstances() as $typeInstance) {
            /** @var $typeInstance \Magento\Catalog\Model\Product\Type\AbstractType */
            if (!$typeInstance->isComposite(null)) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()
            ) {
                $columns = $this->_productIndexerHelper->getFlatColumns();
                $fieldList = array_keys($columns);
                unset($columns['entity_id']);
                unset($columns['child_id']);
                unset($columns['is_child']);
                /** @var $select \Magento\Framework\DB\Select */
                $select = $this->_connection->select()->from(
                    ['t' => $this->_productIndexerHelper->getTable($relation->getTable())],
                    [$relation->getChildFieldName(), new \Zend_Db_Expr('1')]
                )->join(
                    ['entity_table' => $this->_connection->getTableName('catalog_product_entity')],
                    'entity_table.' . $metadata->getLinkField() . 't.' . $relation->getParentFieldName(),
                    [$relation->getParentFieldName() => 'entity_table.entity_id']
                )->join(
                    ['e' => $this->_productIndexerHelper->getFlatTableName($storeId)],
                    "e.entity_id = t.{$relation->getChildFieldName()}",
                    array_keys($columns)
                );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                }
                if ($productIds !== null) {
                    $cond = [
                        $this->_connection->quoteInto("{$relation->getChildFieldName()} IN(?)", $productIds),
                        $this->_connection->quoteInto("entity_table.entity_id IN(?)", $productIds),
                    ];

                    $select->where(implode(' OR ', $cond));
                }
                $sql = $select->insertFromSelect($this->_productIndexerHelper->getFlatTableName($storeId), $fieldList);
                $this->_connection->query($sql);
            }
        }

        return $this;
    }

    /**
     * Clean unused relation products
     *
     * @param int $storeId
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     * @since 2.0.0
     */
    protected function _cleanRelationProducts($storeId)
    {
        if (!$this->_productIndexerHelper->isAddChildData()) {
            return $this;
        }

        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);

        foreach ($this->_getProductTypeInstances() as $typeInstance) {
            /** @var $typeInstance \Magento\Catalog\Model\Product\Type\AbstractType */
            if (!$typeInstance->isComposite(null)) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation && $relation->getTable() && $relation->getParentFieldName() && $relation->getChildFieldName()
            ) {
                $select = $this->_connection->select()->distinct(
                    true
                )->from(
                    ['t' => $this->_productIndexerHelper->getTable($relation->getTable())],
                    []
                )->join(
                    ['entity_table' => $this->_connection->getTableName('catalog_product_entity')],
                    'entity_table.' . $metadata->getLinkField() . 't.' . $relation->getParentFieldName(),
                    [$relation->getParentFieldName() => 'entity_table.entity_id']
                );
                $joinLeftCond = [
                    "e.entity_id = entity_table.entity_id",
                    "e.child_id = t.{$relation->getChildFieldName()}",
                ];
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                    $joinLeftCond[] = $relation->getWhere();
                }

                $entitySelect = new \Zend_Db_Expr($select->__toString());
                /** @var $select \Magento\Framework\DB\Select */
                $select = $this->_connection->select()->from(
                    ['e' => $this->_productIndexerHelper->getFlatTableName($storeId)],
                    null
                )->joinLeft(
                    ['t' => $this->_productIndexerHelper->getTable($relation->getTable())],
                    implode(' AND ', $joinLeftCond),
                    []
                )->where(
                    'e.is_child = ?',
                    1
                )->where(
                    'e.entity_id IN(?)',
                    $entitySelect
                )->where(
                    "t.{$relation->getChildFieldName()} IS NULL"
                );

                $sql = $select->deleteFromSelect('e');
                $this->_connection->query($sql);
            }
        }

        return $this;
    }

    /**
     * Check is flat table for store exists
     *
     * @param int $storeId
     * @return bool
     * @since 2.0.0
     */
    protected function _isFlatTableExists($storeId)
    {
        if (!isset($this->_flatTablesExist[$storeId])) {
            $tableName = $this->_productIndexerHelper->getFlatTableName($storeId);
            $isTableExists = $this->_connection->isTableExists($tableName);

            $this->_flatTablesExist[$storeId] = $isTableExists ? true : false;
        }

        return $this->_flatTablesExist[$storeId];
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     * @since 2.1.0
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
