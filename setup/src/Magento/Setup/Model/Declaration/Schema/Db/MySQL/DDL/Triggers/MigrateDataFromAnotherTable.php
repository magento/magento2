<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\DDL\Triggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\SelectFactory;
use Magento\Setup\Model\Declaration\Schema\Db\DDLTriggerInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Used to migrate data from one column to another in scope of one table.
 * Can add statement in case when data can`t be migrate easily.
 */
class MigrateDataFromAnotherTable implements DDLTriggerInterface
{
    /**
     * Pattern with which we can match whether we can apply and use this trigger or not.
     */
    const MATCH_PATTERN = '/migrateDataFromAnotherTable\(([^\)]+)\,([^\)]+)\)/';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SelectFactory
     */
    private $selectFactory;

    /**
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param SelectFactory $selectFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SelectFactory $selectFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->selectFactory = $selectFactory;
    }

    /**
     * @inheritdoc
     */
    public function isApplicable($statement)
    {
        return preg_match(self::MATCH_PATTERN, $statement);
    }

    /**
     * @param Column $column
     * @inheritdoc
     */
    public function getCallback(ElementInterface $column)
    {
        preg_match(self::MATCH_PATTERN, $column->getOnCreate(), $matches);
        return function () use ($column, $matches) {
            $tableName = $column->getTable()->getName();
            $tableMigrateFrom = $matches[1];
            $columnMigrateFrom = $matches[2];
            $adapter = $this->resourceConnection->getConnection(
                $column->getTable()->getResource()
            );
            $select = $this->selectFactory->create($adapter);
            $select->from(
                $this->resourceConnection->getTableName($tableMigrateFrom),
                [$column->getName() => $columnMigrateFrom]
            );
            //Update only if table exists
            if ($adapter->isTableExists($tableMigrateFrom)) {
                $adapter->query(
                    $adapter->insertFromSelect(
                        $select,
                        $this->resourceConnection->getTableName($tableName)
                    )
                );
            }
        };
    }
}
