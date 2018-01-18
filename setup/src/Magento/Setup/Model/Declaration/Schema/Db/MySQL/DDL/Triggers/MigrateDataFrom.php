<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\DDL\Triggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Setup\Model\Declaration\Schema\Db\DDLTriggerInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Used to migrate data from one column to another in scope of one table
 * Also can add statement in case when data can`t be migrate easily
 */
class MigrateDataFrom implements DDLTriggerInterface
{
    /**
     * Pattern with which we can match whether we can apply and use this trigger or not
     */
    const MATCH_PATTERN = '/migrateDataFrom\(([^\)]+)\)/';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
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
        return function() use ($column, $matches) {
            $tableName = $column->getTable()->getName();
            $adapter = $this->resourceConnection->getConnection(
                $column->getTable()->getResource()
            );
            $adapter
                ->update(
                $this->resourceConnection->getTableName($tableName),
                [
                    $column->getName() => new Expression($matches[1])
                ]
            );
        };
    }
}
