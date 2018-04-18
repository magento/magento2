<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class TemporaryTableService creates a temporary table in mysql from a Magento\Framework\DB\Select.
 * Use this class to create an index with that that you want to query later for quick data access.
 *
 * @api
 */
class TemporaryTableService
{
    /**
     * Default index method.
     */
    const INDEX_METHOD_HASH = 'HASH';

    /**
     * Default engine.
     */
    const DB_ENGINE_INNODB = 'INNODB';

    /**
     * Allowed index methods array.
     *
     * @var string[]
     */
    private $allowedIndexMethods;

    /**
     * Allowed engines array.
     *
     * @var string[]
     */
    private $allowedEngines;

    /**
     * Random data generator.
     *
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * ASrray of Magento Database Adapter Interface.
     * @var AdapterInterface[]
     */
    private $createdTableAdapters = [];

    /**
     * @param \Magento\Framework\Math\Random $random
     * @param string[] $allowedIndexMethods
     * @param string[] $allowedEngines
     */
    public function __construct(
        \Magento\Framework\Math\Random $random,
        $allowedIndexMethods = [],
        $allowedEngines = []
    ) {
        $this->random = $random;
        $this->allowedIndexMethods = $allowedIndexMethods;
        $this->allowedEngines = $allowedEngines;
    }

    /**
     * Creates a temporary table from select removing duplicate rows if you have a union in your select.
     * This method should always be paired with dropTable to ensure cleanup.
     * Make sure you index your data so you can query it fast.
     * You can choose from memory or file table and provide indexes to ensure fast data query.
     *
     * Example: createFromSelect(
     *           $selectObject,
     *           $this->resourceConnection->getConnection(),
     *           [
     *              'PRIMARY' => ['primary_id'],
     *              'some_single_field_index' => ['field'],
     *              'UNQ_some_multiple_field_index' => ['field1', 'field2'],
     *           ]
     *          )
     * Note that indexes names with UNQ_ prefix, will be created as unique.
     *
     * @param Select $select
     * @param AdapterInterface $adapter
     * @param array $indexes
     * @param string $indexMethod
     * @param string $dbEngine
     * @return string
     * @throws \InvalidArgumentException
     */
    public function createFromSelect(
        Select $select,
        AdapterInterface $adapter,
        array $indexes = [],
        $indexMethod = self::INDEX_METHOD_HASH,
        $dbEngine = self::DB_ENGINE_INNODB
    ) {
        if (!in_array($indexMethod, $this->allowedIndexMethods)) {
            throw new \InvalidArgumentException(
                sprintf('indexMethod must be one of %s', implode(',', $this->allowedIndexMethods))
            );
        }

        if (!in_array($dbEngine, $this->allowedEngines)) {
            throw new \InvalidArgumentException(
                sprintf('dbEngine must be one of %s', implode(',', $this->allowedEngines))
            );
        }

        $name = $this->random->getUniqueHash('tmp_select_');

        $indexStatements = [];
        foreach ($indexes as $indexName => $columns) {
            $renderedColumns = implode(',', array_map([$adapter, 'quoteIdentifier'], $columns));

            $indexType = sprintf(
                'INDEX %s USING %s',
                $adapter->quoteIdentifier($indexName),
                $indexMethod
            );

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
            $adapter->quoteIdentifier($dbEngine),
            "{$select}"
        );

        $adapter->query(
            $statement,
            $select->getBind()
        );

        $this->createdTableAdapters[$name] = $adapter;

        return $name;
    }

    /**
     * Method used to drop a table by name.
     * This class will hold all temporary table names in createdTableAdapters array
     * so we can dispose them once we're finished.
     *
     * Example: dropTable($name)
     * where $name is a variable that holds the name for a previously created temporary  table
     * by using "createFromSelect" method.
     *
     * @param string $name
     * @return bool
     */
    public function dropTable($name)
    {
        if (!empty($this->createdTableAdapters)) {
            if (isset($this->createdTableAdapters[$name]) && !empty($name)) {
                $adapter = $this->createdTableAdapters[$name];
                $adapter->dropTemporaryTable($name);
                unset($this->createdTableAdapters[$name]);

                return true;
            }
        }

        return false;
    }
}
