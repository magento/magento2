<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DDL\Triggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\DDLTriggerInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;

/**
 * Used to migrate all the data from one table to another one
 */
class MigrateDataFromAnotherTable implements DDLTriggerInterface
{
    /**
     * Pattern with which we can match whether we can apply and use this trigger or not.
     */
    const MATCH_PATTERN = '/migrateDataFromAnotherTable\(([^\)]+)\)/';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function isApplicable(string $statement) : bool
    {
        return (bool) preg_match(self::MATCH_PATTERN, $statement);
    }

    /**
     * @inheritdoc
     */
    public function getCallback(ElementHistory $tableHistory) : callable
    {
        /** @var Table $table */
        $table = $tableHistory->getNew();
        preg_match(self::MATCH_PATTERN, $table->getOnCreate(), $matches);
        return function () use ($table, $matches) {
            $tableName = $table->getName();
            $oldTableName = $this->resourceConnection->getTableName($matches[1]);
            $adapter = $this->resourceConnection->getConnection($table->getResource());
            $select = $adapter->select()->from($oldTableName);
            $adapter->query($adapter->insertFromSelect($select, $tableName));
        };
    }
}
