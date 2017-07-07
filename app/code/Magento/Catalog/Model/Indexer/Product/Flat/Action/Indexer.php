<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class Indexer
 */
class Indexer
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * Maximum size of attributes chunk
     */
    const ATTRIBUTES_CHUNK_SIZE = 59;

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_productIndexerHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
    ) {
        $this->_productIndexerHelper = $productHelper;
        $this->_connection = $resource->getConnection();
    }

    /**
     * Write single product into flat product table
     *
     * @param int $storeId
     * @param int $productId
     * @param string $valueFieldSuffix
     * @return \Magento\Catalog\Model\Indexer\Product\Flat
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function write($storeId, $productId, $valueFieldSuffix = '')
    {
        $flatTable = $this->_productIndexerHelper->getFlatTableName($storeId);

        $attributes = $this->_productIndexerHelper->getAttributes();
        $eavAttributes = $this->_productIndexerHelper->getTablesStructure($attributes);
        $updateData = [];
        $describe = $this->_connection->describeTable($flatTable);

        foreach ($eavAttributes as $tableName => $tableColumns) {
            $columnsChunks = array_chunk($tableColumns, self::ATTRIBUTES_CHUNK_SIZE, true);

            foreach ($columnsChunks as $columns) {
                $select = $this->_connection->select();
                $selectValue = $this->_connection->select();
                $keyColumns = [
                    'entity_id' => 'e.entity_id',
                    'attribute_id' => 't.attribute_id',
                    'value' => $this->_connection->getIfNullSql('`t2`.`value`', '`t`.`value`'),
                ];

                if ($tableName != $this->_productIndexerHelper->getTable('catalog_product_entity')) {
                    $valueColumns = [];
                    $ids = [];
                    $select->from(
                        ['e' => $this->_productIndexerHelper->getTable('catalog_product_entity')],
                        $keyColumns
                    );

                    $selectValue->from(
                        ['e' => $this->_productIndexerHelper->getTable('catalog_product_entity')],
                        $keyColumns
                    );

                    /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                    foreach ($columns as $columnName => $attribute) {
                        if (isset($describe[$columnName])) {
                            $ids[$attribute->getId()] = $columnName;
                        }
                    }
                    $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
                    $select->joinLeft(
                        ['t' => $tableName],
                        sprintf('e.%s = t.%s ', $linkField, $linkField) . $this->_connection->quoteInto(
                            ' AND t.attribute_id IN (?)',
                            array_keys($ids)
                        ) . ' AND t.store_id = 0',
                        []
                    )->joinLeft(
                        ['t2' => $tableName],
                        sprintf('t.%s = t2.%s ', $linkField, $linkField) .
                        ' AND t.attribute_id = t2.attribute_id  ' .
                        $this->_connection->quoteInto(
                            ' AND t2.store_id = ?',
                            $storeId
                        ),
                        []
                    )->where(
                        'e.entity_id = ' . $productId
                    )->where(
                        't.attribute_id IS NOT NULL'
                    );
                    $cursor = $this->_connection->query($select);
                    while ($row = $cursor->fetch(\Zend_Db::FETCH_ASSOC)) {
                        $updateData[$ids[$row['attribute_id']]] = $row['value'];
                        $valueColumnName = $ids[$row['attribute_id']] . $valueFieldSuffix;
                        if (isset($describe[$valueColumnName])) {
                            $valueColumns[$row['value']] = $valueColumnName;
                        }
                    }

                    //Update not simple attributes (eg. dropdown)
                    if (!empty($valueColumns)) {
                        $valueIds = array_keys($valueColumns);

                        $select = $this->_connection->select()->from(
                            ['t' => $this->_productIndexerHelper->getTable('eav_attribute_option_value')],
                            ['t.option_id', 't.value']
                        )->where(
                            $this->_connection->quoteInto('t.option_id IN (?)', $valueIds)
                        )->where(
                            $this->_connection->quoteInto('t.store_id IN(?)', [
                                \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                $storeId
                            ])
                        )
                        ->order('t.store_id ASC');
                        $cursor = $this->_connection->query($select);
                        while ($row = $cursor->fetch(\Zend_Db::FETCH_ASSOC)) {
                            $valueColumnName = $valueColumns[$row['option_id']];
                            if (isset($describe[$valueColumnName])) {
                                $updateData[$valueColumnName] = $row['value'];
                            }
                        }
                    }
                } else {
                    $columnNames = array_keys($columns);
                    $columnNames[] = 'attribute_set_id';
                    $columnNames[] = 'type_id';
                    $select->from(
                        ['e' => $this->_productIndexerHelper->getTable('catalog_product_entity')],
                        $columnNames
                    )->where(
                        'e.entity_id = ' . $productId
                    );
                    $cursor = $this->_connection->query($select);
                    $row = $cursor->fetch(\Zend_Db::FETCH_ASSOC);
                    if (!empty($row)) {
                        foreach ($row as $columnName => $value) {
                            $updateData[$columnName] = $value;
                        }
                    }
                }
            }
        }

        if (!empty($updateData)) {
            $updateData += ['entity_id' => $productId];
            $updateFields = [];
            foreach ($updateData as $key => $value) {
                $updateFields[$key] = $key;
            }
            $this->_connection->insertOnDuplicate($flatTable, $updateData, $updateFields);
        }

        return $this;
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
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
