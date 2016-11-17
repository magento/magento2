<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

class TemporaryTableService
{
    /**
     * @var AdapterInterface[]
     */
    private $createdTables = [];

    /**
     * Creates a temporary table from select
     *
     * @param Select $select
     * @param AdapterInterface $adapter
     * @param array $indexes
     * @param string $engine
     *
     * @return string
     */
    public function createTemporaryTable(
        Select $select,
        AdapterInterface $adapter,
        array $indexes = [],
        $engine = 'INNODB'
    ) {
        $name = uniqid('tmp_select_' . crc32((string)$select));

        $indexStatements = [];
        foreach ($indexes as $indexName => $columns) {
            $renderedColumns = implode(',', array_map([$adapter, 'quoteIdentifier'], $columns));

            $indexType = sprintf('INDEX %s USING HASH', $adapter->quoteIdentifier($indexName));

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
