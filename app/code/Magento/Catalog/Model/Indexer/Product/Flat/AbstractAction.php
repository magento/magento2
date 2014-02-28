<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

/**
 * Abstract action reindex class
 *
 * @package Magento\Catalog\Model\Indexer\Product\Flat
 */
abstract class AbstractAction
{
    /**
     * Path to maximum available amount of indexes for flat indexer
     */
    const XML_NODE_MAX_INDEX_COUNT  = 'catalog/product/flat/max_index_count';

    /**
     * Maximum size of attributes chunk
     */
    const ATTRIBUTES_CHUNK_SIZE = 59;

    /**
     * Suffix for value field on composite attributes
     *
     * @var string
     */
    protected $_valueFieldSuffix = '_value';

    /**
     * Resource instance
     *
     * @var \Magento\App\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_coreStoreConfig;

    /**
     * Suffix for drop table (uses on flat table rename)
     *
     * @var string
     */
    protected $_tableDropSuffix = '_drop_indexer';

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_productIndexerHelper;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    /**
     * Existing flat tables flags pool
     *
     * @var array
     */
    protected $_flatTablesExist = array();

    /**
     * List of product types available in installation
     *
     * @var array
     */
    protected $_productTypes = array();

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_flatProductProcessor;

    /**
     * @var TableBuilder
     */
    protected $_tableBuilder;

    /**
     * @var FlatTableBuilder
     */
    protected $_flatTableBuilder;

    /**
     * @param \Magento\App\Resource $resource
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param Processor $flatProductProcessor
     * @param TableBuilder $tableBuilder
     * @param FlatTableBuilder $flatTableBuilder
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $flatProductProcessor,
        \Magento\Catalog\Model\Indexer\Product\Flat\TableBuilder $tableBuilder,
        \Magento\Catalog\Model\Indexer\Product\Flat\FlatTableBuilder $flatTableBuilder
    ) {
        $this->_resource = $resource;
        $this->_storeManager = $storeManager;
        $this->_resourceHelper = $resourceHelper;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_productIndexerHelper = $productHelper;
        $this->_productType = $productType;
        $this->_connection = $resource->getConnection('default');
        $this->_flatProductProcessor = $flatProductProcessor;
        $this->_tableBuilder = $tableBuilder;
        $this->_flatTableBuilder = $flatTableBuilder;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    abstract public function execute($ids);

    /**
     * Retrieve Catalog Product Flat Table name
     *
     * @param int $storeId
     * @return string
     */
    public function getFlatTableName($storeId)
    {
        return sprintf('%s_%s', $this->_connection->getTableName('catalog_product_flat'), $storeId);
    }

    /**
     * Return temporary table name by regular table name
     *
     * @param string $tableName
     *
     * @return string
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
     *
     * @throws \Exception
     */
    protected function _reindex($storeId, array $changedIds = array())
    {
        try {
            $this->_tableBuilder->build($storeId, $changedIds, $this->_valueFieldSuffix);
            $this->_flatTableBuilder->build(
                $storeId, $changedIds, $this->_valueFieldSuffix, $this->_tableDropSuffix, true
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
     * Remove products from flat that are not exist
     *
     * @param array $ids
     * @param int $storeId
     */
    protected function _removeDeletedProducts(array &$ids, $storeId)
    {
        $select = $this->_connection->select()
            ->from($this->_productIndexerHelper->getTable('catalog_product_entity'))
            ->where('entity_id IN(?)', $ids);
        $result = $this->_connection->query($select);

        $existentProducts = [];
        foreach ($result->fetchAll()as $product) {
            $existentProducts[] = $product['entity_id'];
        }

        $productsToDelete = array_diff($ids, $existentProducts);
        $ids = $existentProducts;

        $this->deleteProductsFromStore($productsToDelete, $storeId);
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     */
    protected function _getProductTypeInstances()
    {
        if ($this->_productTypes === null) {
            $this->_productTypes = array();
            $productEmulator     = new \Magento\Object();
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
     */
    protected function _updateRelationProducts($storeId, $productIds = null)
    {
        if (!$this->_productIndexerHelper->isAddChildData() || !$this->_isFlatTableExists($storeId)) {
            return $this;
        }

        foreach ($this->_getProductTypeInstances() as $typeInstance) {
            /** @var $typeInstance \Magento\Catalog\Model\Product\Type\AbstractType */
            if (!$typeInstance->isComposite(null)) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation
                && $relation->getTable()
                && $relation->getParentFieldName()
                && $relation->getChildFieldName()
            ) {
                $columns   = $this->_productIndexerHelper->getFlatColumns();
                $fieldList = array_keys($columns);
                unset($columns['entity_id']);
                unset($columns['child_id']);
                unset($columns['is_child']);
                /** @var $select \Magento\DB\Select */
                $select = $this->_connection->select()
                    ->from(
                        array('t' => $this->_productIndexerHelper->getTable($relation->getTable())),
                        array($relation->getParentFieldName(), $relation->getChildFieldName(), new \Zend_Db_Expr('1')))
                    ->join(
                        array('e' => $this->_productIndexerHelper->getFlatTableName($storeId)),
                        "e.entity_id = t.{$relation->getChildFieldName()}",
                        array_keys($columns)
                    );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                }
                if ($productIds !== null) {
                    $cond = array(
                        $this->_connection->quoteInto("{$relation->getChildFieldName()} IN(?)", $productIds),
                        $this->_connection->quoteInto("{$relation->getParentFieldName()} IN(?)", $productIds)
                    );

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
     */
    protected function _cleanRelationProducts($storeId)
    {
        if (!$this->_productIndexerHelper->isAddChildData()) {
            return $this;
        }

        foreach ($this->_getProductTypeInstances() as $typeInstance) {
            /** @var $typeInstance \Magento\Catalog\Model\Product\Type\AbstractType */
            if (!$typeInstance->isComposite(null)) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation
                && $relation->getTable()
                && $relation->getParentFieldName()
                && $relation->getChildFieldName()
            ) {
                $select = $this->_connection->select()
                    ->distinct(true)
                    ->from(
                        $this->_productIndexerHelper->getTable($relation->getTable()),
                        "{$relation->getParentFieldName()}"
                    );
                $joinLeftCond = array(
                    "e.entity_id = t.{$relation->getParentFieldName()}",
                    "e.child_id = t.{$relation->getChildFieldName()}"
                );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                    $joinLeftCond[] = $relation->getWhere();
                }

                $entitySelect = new \Zend_Db_Expr($select->__toString());
                /** @var $select \Magento\DB\Select */
                $select = $this->_connection->select()
                    ->from(array('e' => $this->_productIndexerHelper->getFlatTableName($storeId)), null)
                    ->joinLeft(
                        array('t' => $this->_productIndexerHelper->getTable($relation->getTable())),
                        implode(' AND ', $joinLeftCond),
                        array()
                    )
                    ->where('e.is_child = ?', 1)
                    ->where('e.entity_id IN(?)', $entitySelect)
                    ->where("t.{$relation->getChildFieldName()} IS NULL");

                $sql = $select->deleteFromSelect('e');
                $this->_connection->query($sql);
            }
        }

        return $this;
    }

    /**
     * Reindex single product into flat product table
     *
     * @param int $storeId
     * @param int $productId
     * @return \Magento\Catalog\Model\Indexer\Product\Flat
     */
    protected function _reindexSingleProduct($storeId, $productId)
    {
        $flatTable = $this->_productIndexerHelper->getFlatTableName($storeId);

        if (!$this->_connection->isTableExists($flatTable)) {
            $this->_flatTableBuilder->build(
                $storeId, array($productId), $this->_valueFieldSuffix, $this->_tableDropSuffix, false
            );
        }

        $attributes    = $this->_productIndexerHelper->getAttributes();
        $eavAttributes = $this->_productIndexerHelper->getTablesStructure($attributes);
        $updateData    = array();
        $describe      = $this->_connection->describeTable($flatTable);

        foreach ($eavAttributes as $tableName => $tableColumns) {
            $columnsChunks = array_chunk($tableColumns, self::ATTRIBUTES_CHUNK_SIZE, true);

            foreach ($columnsChunks as $columns) {
                $select      = $this->_connection->select();
                $selectValue = $this->_connection->select();
                $keyColumns  = array(
                    'entity_id'    => 'e.entity_id',
                    'attribute_id' => 't.attribute_id',
                    'value'        =>  $this->_connection->getIfNullSql('`t2`.`value`', '`t`.`value`'),
                );

                if ($tableName != $this->_productIndexerHelper->getTable('catalog_product_entity')) {
                    $valueColumns = array();
                    $ids          = array();
                    $select->from(
                        array('e' => $this->_productIndexerHelper->getTable('catalog_product_entity')),
                        $keyColumns
                    );

                    $selectValue->from(
                        array('e' => $this->_productIndexerHelper->getTable('catalog_product_entity')),
                        $keyColumns
                    );

                    /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
                    foreach ($columns as $columnName => $attribute) {
                        if (isset($describe[$columnName])) {
                            $ids[$attribute->getId()] = $columnName;
                        }
                    }

                    $select->joinLeft(
                        array('t' => $tableName),
                        'e.entity_id = t.entity_id '
                        . $this->_connection->quoteInto(' AND t.attribute_id IN (?)', array_keys($ids))
                        . ' AND t.store_id = 0',
                        array()
                    )->joinLeft(
                            array('t2' => $tableName),
                            't.entity_id = t2.entity_id '
                            . ' AND t.attribute_id = t2.attribute_id  '
                            . $this->_connection->quoteInto(' AND t2.store_id = ?', $storeId),
                            array()
                        )->where(
                            'e.entity_id = ' . $productId
                        )->where(
                            't.attribute_id IS NOT NULL'
                        );
                    $cursor = $this->_connection->query($select);
                    while ($row = $cursor->fetch(\Zend_Db::FETCH_ASSOC)) {
                        $updateData[$ids[$row['attribute_id']]] = $row['value'];
                        $valueColumnName = $ids[$row['attribute_id']] . $this->_valueFieldSuffix;
                        if (isset($describe[$valueColumnName])) {
                            $valueColumns[$row['value']] = $valueColumnName;
                        }
                    }

                    //Update not simple attributes (eg. dropdown)
                    if (!empty($valueColumns)) {
                        $valueIds = array_keys($valueColumns);

                        $select = $this->_connection->select()
                            ->from(
                                array('t' => $this->_productIndexerHelper->getTable('eav_attribute_option_value')),
                                array('t.option_id', 't.value')
                            )->where(
                                $this->_connection->quoteInto('t.option_id IN (?)', $valueIds)
                            );
                        $cursor = $this->_connection->query($select);
                        while ($row = $cursor->fetch(\Zend_Db::FETCH_ASSOC)) {
                            $valueColumnName = $valueColumns[$row['option_id']];
                            if (isset($describe[$valueColumnName])) {
                                $updateData[$valueColumnName] = $row['value'];
                            }
                        }
                    }

                } else {
                    $columnNames   = array_keys($columns);
                    $columnNames[] = 'attribute_set_id';
                    $columnNames[] = 'type_id';
                    $select->from(
                        array('e' => $this->_productIndexerHelper->getTable('catalog_product_entity')),
                        $columnNames
                    )->where(
                            'e.entity_id = ' . $productId
                        );
                    $cursor = $this->_connection->query($select);
                    $row    = $cursor->fetch(\Zend_Db::FETCH_ASSOC);
                    if (!empty($row)) {
                        foreach ($row as $columnName => $value) {
                            $updateData[$columnName] = $value;
                        }
                    }
                }
            }
        }

        if (!empty($updateData)) {
            $updateData += array('entity_id' => $productId);
            $updateFields = array();
            foreach ($updateData as $key => $value) {
                $updateFields[$key] = $key;
            }
            $this->_connection->insertOnDuplicate($flatTable, $updateData, $updateFields);
        }

        return $this;
    }

    /**
     * Delete products from flat table(s)
     *
     * @param int|array $productId
     * @param null|int $storeId
     */
    public function deleteProductsFromStore($productId, $storeId = null)
    {
        if (!is_array($productId)) {
            $productId = array($productId);
        }
        if (null === $storeId) {
            foreach ($this->_storeManager->getStores() as $store) {
                $this->_connection->delete(
                    $this->_productIndexerHelper->getFlatTableName($store->getId()),
                    array('entity_id IN(?)' => $productId)
                );
            }
        } else {
            $this->_connection->delete(
                $this->_productIndexerHelper->getFlatTableName((int)$storeId),
                array('entity_id IN(?)' => $productId)
            );
        }
    }

    /**
     * Check is flat table for store exists
     *
     * @param int $storeId
     * @return bool
     */
    protected function _isFlatTableExists($storeId)
    {
        if (!isset($this->_flatTablesExist[$storeId])) {
            $tableName     = $this->getFlatTableName($storeId);
            $isTableExists = $this->_connection->isTableExists($tableName);

            $this->_flatTablesExist[$storeId] = $isTableExists ? true : false;
        }

        return $this->_flatTablesExist[$storeId];
    }

    /**
     * set valid state
     *
     * @param $versionId
     */
    protected function _setValidState($versionId)
    {
        $this->_flatProductProcessor
            ->getIndexer()
            ->getView()
            ->getState()
            ->setStatus(\Magento\Indexer\Model\Indexer\State::STATUS_VALID)
            ->setVersionId($versionId)
            ->save();
        $this->_flatProductProcessor
            ->getIndexer()
            ->getView()
            ->getChangelog()
            ->clear($versionId);
    }

    /**
     * Set invalid state
     */
    protected function _setInvalidState()
    {
        $this->_flatProductProcessor->markIndexerAsInvalid();
    }

    /**
     * set processing state
     */
    protected function _setProcessingState()
    {
        $this->_flatProductProcessor
            ->getIndexer()
            ->getState()
            ->setStatus(\Magento\Indexer\Model\Indexer\State::STATUS_WORKING);
    }

    /**
     * Is indexer processing
     *
     * @return bool
     */
    protected function _isProcessing()
    {
        return $this->_flatProductProcessor->getIndexer()->isWorking();
    }
}
