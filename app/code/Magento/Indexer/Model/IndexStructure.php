<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model;


use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class IndexStructure
{
    /**
     * @var \Magento\Framework\App\Resource
     */
    private $resource;

    /**
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(\Magento\Framework\App\Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param string $table
     * @param array $dimensions
     */
    public function delete($table, array $dimensions)
    {
        $adapter = $this->getAdapter();
        foreach ($dimensions as $dimension) {
            $tableName = $table . $dimension;
            $this->dropTable($adapter, $tableName);
            $this->dropTable($adapter, $this->getFulltextTableName($tableName));
        }
    }

    /**
     * @param string $table
     * @param array $filterFields
     * @param array $dimensions
     */
    public function create($table, array $filterFields, array $dimensions)
    {
        foreach ($dimensions as $dimension) {
            $tableName = $table . $dimension;
            $this->createFulltextIndex($this->getFulltextTableName($tableName));
            if ($filterFields) {
                $this->createFlatIndex($tableName, $filterFields);
            }
        }
    }

    /**
     * @param $tableName
     * @throws \Zend_Db_Exception
     */
    protected function createFulltextIndex($tableName)
    {
        $adapter = $this->getAdapter();
        $table = $adapter->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false],
                'Product ID'
            )->addColumn(
                'attribute_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false]
            )->addColumn(
                'data_index',
                Table::TYPE_TEXT,
                '4g',
                ['nullable' => true],
                'Data index'
            )->addIndex(
                'idx_primary',
                ['entity_id', 'attribute_id'],
                ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
            )->addIndex(
                'FTI_FULLTEXT_DATA_INDEX',
                ['data_index'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            );
        $adapter->createTable($table);
    }

    /**
     * @param $tableName
     * @param $fields
     * @throws \Zend_Db_Exception
     */
    protected function createFlatIndex($tableName, $fields)
    {
        $adapter = $this->getAdapter();
        $table = $adapter->newTable($tableName);
        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'];
            $size = $field['size'];
            $table->addColumn($name, $type, $size);
        }
        $adapter->createTable($table);
    }


    /**
     * @return false|AdapterInterface
     */
    private function getAdapter()
    {
        $adapter = $this->resource->getConnection('write');
        return $adapter;
    }

    /**
     * @param AdapterInterface $adapter
     * @param string $tableName
     */
    private function dropTable(AdapterInterface $adapter, $tableName)
    {
        if ($adapter->isTableExists($tableName)) {
            $adapter->dropTable($tableName);
        }
    }

    /**
     * @param $tableName
     * @return string
     */
    private function getFulltextTableName($tableName)
    {
        return $tableName . '_fulltext';
    }
}
