<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model;


use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\Dimension;

class IndexStructure
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param string $table
     * @param Dimension[] $dimensions
     */
    public function delete($table, array $dimensions)
    {
        $adapter = $this->getAdapter();
        foreach ($dimensions as $dimension) {
            $this->dropTable($adapter, $this->getFlatTableName($table, $dimension));
            $this->dropTable($adapter, $this->getFulltextTableName($table, $dimension));
        }
    }

    /**
     * @param string $table
     * @param array $filterFields
     * @param Dimension[] $dimensions
     */
    public function create($table, array $filterFields, array $dimensions)
    {
        foreach ($dimensions as $dimension) {
            $this->createFulltextIndex($this->getFulltextTableName($table, $dimension));
            if ($filterFields) {
                $this->createFlatIndex($this->getFlatTableName($table, $dimension), $filterFields);
            }
        }
    }

    /**
     * @param string $tableName
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
                'Entity ID'
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
     * @param string $tableName
     * @param array $fields
     * @throws \Zend_Db_Exception
     */
    protected function createFlatIndex($tableName, array $fields)
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
     * @param $table
     * @param Dimension $dimension
     * @return string
     */
    private function getFulltextTableName($table, Dimension $dimension)
    {
        return $this->getFlatTableName($table, $dimension) . '_fulltext';
    }

    /**
     * @param string $table
     * @param Dimension $dimension
     * @return string
     */
    private function getFlatTableName($table, Dimension $dimension)
    {
        $tableName = $table . '_' . $dimension->getName() . '_' . $dimension->getValue();
        return $tableName;
    }
}
