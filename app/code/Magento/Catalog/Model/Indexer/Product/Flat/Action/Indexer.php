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
use Magento\Store\Model\Store;

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
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function write($storeId, $productId, $valueFieldSuffix = '')
    {
        $flatTable = $this->_productIndexerHelper->getFlatTableName($storeId);
        $entityTableName = $this->_productIndexerHelper->getTable('catalog_product_entity');

        $attributes = $this->_productIndexerHelper->getAttributes();
        $eavAttributes = $this->_productIndexerHelper->getTablesStructure($attributes);
        $updateData = [];
        $describe = $this->_connection->describeTable($flatTable);
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        foreach ($eavAttributes as $tableName => $tableColumns) {
            $columnsChunks = array_chunk($tableColumns, self::ATTRIBUTES_CHUNK_SIZE, true);

            foreach ($columnsChunks as $columns) {
                $select = $this->_connection->select();

                if ($tableName != $entityTableName) {
                    $valueColumns = [];
                    $ids = [];
                    $select->from(
                        ['e' => $entityTableName],
                        [
                            'entity_id' => 'e.entity_id',
                            'attribute_id' => 't.attribute_id',
                            'value' => 't.value'
                        ]
                    );

                    /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                    foreach ($columns as $columnName => $attribute) {
                        if (isset($describe[$columnName])) {
                            $ids[$attribute->getId()] = $columnName;
                        }
                    }
                    $select->joinInner(
                        ['t' => $tableName],
                        sprintf('e.%s = t.%s ', $linkField, $linkField) . $this->_connection->quoteInto(
                            ' AND t.attribute_id IN (?)',
                            array_keys($ids)
                        ) . ' AND ' . $this->_connection->quoteInto('t.store_id IN(?)', [
                                Store::DEFAULT_STORE_ID,
                                $storeId
                            ]),
                        []
                    )->where(
                        'e.entity_id = ' . $productId
                    )->order('t.store_id ASC');
                    $cursor = $this->_connection->query($select);
                    while ($row = $cursor->fetch(\Zend_Db::FETCH_ASSOC)) {
                        $updateData[$ids[$row['attribute_id']]] = $row['value'];
                        $valueColumnName = $ids[$row['attribute_id']] . $valueFieldSuffix;
                        if (isset($describe[$valueColumnName])) {
                            $valueColumns[$row['attribute_id']] = [
                                'value' => $row['value'],
                                'column_name' => $valueColumnName
                            ];
                        }
                    }

                    //Update not simple attributes (eg. dropdown)
                    if (!empty($valueColumns)) {
                        $valueIds = array_column($valueColumns, 'value');
                        $optionIdToAttributeName = array_column($valueColumns, 'column_name', 'value');

                        $select = $this->_connection->select()->from(
                            ['t' => $this->_productIndexerHelper->getTable('eav_attribute_option_value')],
                            ['t.option_id', 't.value']
                        )->where(
                            $this->_connection->quoteInto('t.option_id IN (?)', $valueIds)
                        )->where(
                            $this->_connection->quoteInto('t.store_id IN(?)', [
                                Store::DEFAULT_STORE_ID,
                                $storeId
                            ])
                        )
                        ->order('t.store_id ASC');
                        $cursor = $this->_connection->query($select);
                        while ($row = $cursor->fetch(\Zend_Db::FETCH_ASSOC)) {
                            $valueColumnName = $optionIdToAttributeName[$row['option_id']];
                            if (isset($describe[$valueColumnName])) {
                                $updateData[$valueColumnName] = $row['value'];
                            }
                        }
                    }
                } else {
                    $columnNames = array_keys($columns);
                    $columnNames[] = 'attribute_set_id';
                    $columnNames[] = 'type_id';
                    $columnNames[] = $linkField;
                    $select->from(
                        ['e' => $entityTableName],
                        $columnNames
                    )->where(
                        'e.entity_id = ' . $productId
                    );
                    $cursor = $this->_connection->query($select);
                    $row = $cursor->fetch(\Zend_Db::FETCH_ASSOC);
                    if (!empty($row)) {
                        $linkFieldId = $linkField;
                        foreach ($row as $columnName => $value) {
                            $updateData[$columnName] = $value;
                        }
                    }
                }
            }
        }

        if (!empty($updateData)) {
            $updateData += ['entity_id' => $productId];
            if ($linkField !== $metadata->getIdentifierField()) {
                $updateData += [$linkField => $linkFieldId];
            }
            $updateFields = [];
            foreach ($updateData as $key => $value) {
                $updateFields[$key] = $key;
            }
            $this->_connection->insertOnDuplicate($flatTable, $updateData, $updateFields);
        }

        return $this;
    }

    /**
     * Get MetadataPool instance
     *
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
