<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Class TemporaryTableService creates a temporary table in mysql from a Magento\Framework\DB\Select.
 * Use this class to create an index with that that you want to query later for quick data access
 *
 * @api
 */
class TemporaryTableService
{
    /**
     * @var AdapterInterface[]
     */
    private $createdTables = [];

    /**
     * Creates a temporary table from select removing duplicate rows if you have a union in your select
     * This method should always be paired with dropTable to ensure cleanup
     * Make sure you index your data so you can query it fast
     * You can choose from memory or file table and provide indexes to ensure fast data query
     *
     * Example: createFromSelect(
     *           $selectObject,
     *           $this->resourceConnection->getConnection(),
     *           [
     *              'PRIMARY' => ['primary_id'],
     *              'some_single_field_index' => ['field'],
     *              'some_multiple_field_index' => ['field1', 'field2'],
     *           ]
     *          )
     *
     * @param Select $select
     * @param AdapterInterface $adapter
     * @param array $indexes
     * @param string $indexMethod
     * @param string $engine
     * @return string
     */
    public function createFromSelect(
        Select $select,
        AdapterInterface $adapter,
        array $indexes = [],
        $indexMethod = 'HASH',
        $engine = 'INNODB'
    ) {
        $name = uniqid('tmp_select_' . crc32((string)$select));

        $indexStatements = [];
        foreach ($indexes as $indexName => $columns) {
            $renderedColumns = implode(',', array_map([$adapter, 'quoteIdentifier'], $columns));

            $indexType = sprintf('INDEX %s USING %s', $adapter->quoteIdentifier($indexName), $indexMethod);

            if ($indexName === 'PRIMARY') {
                $indexType = 'PRIMARY KEY';
            } elseif (strpos($indexName, 'UNQ_') === 0) {
                $indexType = sprintf('UNIQUE %s', $adapter->quoteIdentifier($indexName));
            }

            $indexStatements[] = sprintf('%s(%s)', $indexType, $renderedColumns);
        }

        $statement = sprintf(
            'CREATE TEMPORARY TABLE %s %s ENGINE=%s IGNORE (%s)',
            $adapter->quoteIdentifier($name),
            $indexStatements ? '(' . implode(',', $indexStatements) . ')' : '',
            $engine,
            (string)$select
        );

        $adapter->query(
            $statement,
            $select->getBind()
        );

        $this->createdTables[$name] = $adapter;

        return $name;
    }

    /**
     * Method used to drop a table by name
     * This class will hold all temporary table names in createdTables array so we can dispose them once we're finished
     *
     * Example: dropTable($previouslySavedTableName)
     * where $previouslySavedTableName is a variable that you have to save when you use "createFromSelect" method
     *
     * @param string $name
     * @return bool
     */
    public function dropTable($name)
    {
        if (!empty($this->createdTables)) {
            if (isset($this->createdTables[$name]) && !empty($name)) {
                $adapter = $this->createdTables[$name];
                $adapter->dropTemporaryTable($name);
                unset($this->createdTables[$name]);
                return true;
            }
        }
        return false;
    }
}
