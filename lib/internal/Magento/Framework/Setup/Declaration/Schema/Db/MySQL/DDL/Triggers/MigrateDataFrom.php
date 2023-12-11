<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DDL\Triggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Setup\Declaration\Schema\Db\DDLTriggerInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;

/**
 * Used to migrate data from one column to another in scope of one table.
 * Also can add statement in case when data can`t be migrate easily.
 */
class MigrateDataFrom implements DDLTriggerInterface
{
    /**
     * Pattern with which we can match whether we can apply and use this trigger or not.
     */
    public const MATCH_PATTERN = '/migrateDataFrom\(([^\)]+)\)/';

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
    public function getCallback(ElementHistory $columnHistory) : callable
    {
        /** @var Column $column */
        $column = $columnHistory->getNew();
        preg_match(self::MATCH_PATTERN, $column->getOnCreate() ?? '', $matches);
        return function () use ($column, $matches) {
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
