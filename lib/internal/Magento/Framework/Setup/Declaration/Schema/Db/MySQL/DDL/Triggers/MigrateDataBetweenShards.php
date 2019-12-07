<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DDL\Triggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\DataSavior\SelectGenerator;
use Magento\Framework\Setup\Declaration\Schema\Db\DDLTriggerInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;

/**
 * Used to migrate data from one column to another in scope of one table.
 * Also can add statement in case when data can`t be migrate easily.
 */
class MigrateDataBetweenShards implements DDLTriggerInterface
{
    /**
     * This flag says, whether we should to skip data migration from one shard to another
     */
    const SKIP_MIGRATION_DATA_FLAG = 'skip-migration';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SelectGenerator
     */
    private $selectGenerator;

    /**
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param SelectGenerator $selectGenerator
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SelectGenerator $selectGenerator
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->selectGenerator = $selectGenerator;
    }

    /**
     * If skip migration flag is enabled, we should skip data migration
     *
     * @inheritdoc
     */
    public function isApplicable(string $statement) : bool
    {
        return $statement !== self::SKIP_MIGRATION_DATA_FLAG;
    }

    /**
     * @inheritdoc
     */
    public function getCallback(ElementHistory $elementHistory) : callable
    {
        /** @var Table $newTable */
        $newTable = $elementHistory->getNew();
        /** @var Table $oldTable */
        $oldTable = $elementHistory->getOld();
        $that = $this;

        return function () use ($newTable, $oldTable, $that) {
            $firstConnection = $that->resourceConnection->getConnection($oldTable->getResource());
            $secondConnection = $that->resourceConnection->getConnection($newTable->getResource());
            $select = $firstConnection->select()->from($oldTable->getName());

            foreach ($this->selectGenerator->generator($select, $oldTable->getResource()) as $data) {
                if (count($data)) {
                    $columns = array_keys($data[0]);
                    $secondConnection->insertArray($newTable->getName(), $columns, $data);
                }
            }
        };
    }
}
