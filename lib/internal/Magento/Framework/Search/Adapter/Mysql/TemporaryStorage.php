<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;

/**
 * @api
 */
class TemporaryStorage
{
    const TEMPORARY_TABLE_PREFIX = 'search_tmp_';

    const FIELD_ENTITY_ID = 'entity_id';
    const FIELD_SCORE = 'score';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Stores Documents
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface[] $documents
     * @return Table
     * @deprecated
     */
    public function storeDocuments($documents)
    {
        return $this->storeApiDocuments($documents);
    }

    /**
     * Stores Api type Documents
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface[] $documents
     * @return Table
     */
    public function storeApiDocuments($documents)
    {
        $data = [];
        foreach ($documents as $document) {
            $data[] = [
                $document->getId(),
                $document->getCustomAttribute('score')->getValue(),
            ];
        }

        return $this->populateTemporaryTable($this->createTemporaryTable(), $data);
    }

    /**
     * Populates temporary table
     *
     * @param Table $table
     * @param array $data
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function populateTemporaryTable(Table $table, $data)
    {
        if (count($data)) {
            $this->getConnection()->insertArray(
                $table->getName(),
                [
                    self::FIELD_ENTITY_ID,
                    self::FIELD_SCORE,
                ],
                $data
            );
        }
        return $table;
    }

    /**
     * @param Select $select
     * @return Table
     * @throws \Zend_Db_Exception
     */
    public function storeDocumentsFromSelect(Select $select)
    {
        $table = $this->createTemporaryTable();
        $this->getConnection()->query($this->getConnection()->insertFromSelect($select, $table->getName()));
        return $table;
    }

    /**
     * @return false|AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTemporaryTable()
    {
        $connection = $this->getConnection();
        $tableName = $this->resource->getTableName(str_replace('.', '_', uniqid(self::TEMPORARY_TABLE_PREFIX, true)));
        $table = $connection->newTable($tableName);
        $connection->dropTemporaryTable($table->getName());
        $table->addColumn(
            self::FIELD_ENTITY_ID,
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        );
        $table->addColumn(
            self::FIELD_SCORE,
            Table::TYPE_DECIMAL,
            [32, 16],
            ['unsigned' => true, 'nullable' => false],
            'Score'
        );
        $table->setOption('type', 'memory');
        $connection->createTemporaryTable($table);
        return $table;
    }
}
