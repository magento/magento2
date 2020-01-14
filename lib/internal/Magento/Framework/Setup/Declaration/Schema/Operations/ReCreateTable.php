<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Operations;

use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DDL\Triggers\MigrateDataBetweenShards;
use Magento\Framework\Setup\Declaration\Schema\Db\Statement;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\ElementHistoryFactory;
use Magento\Framework\Setup\Declaration\Schema\OperationInterface;

/**
 * Recreate table operation.
 * Drops and creates table again.
 */
class ReCreateTable implements OperationInterface
{
    /**
     * Operation name.
     */
    const OPERATION_NAME = 'recreate_table';

    /**
     * @var CreateTable
     */
    private $createTable;

    /**
     * @var DropTable
     */
    private $dropTable;

    /**
     * @var MigrateDataBetweenShards
     */
    private $migrateDataBetweenShards;

    /**
     * @var ElementHistoryFactory
     */
    private $elementHistoryFactory;

    /**
     * @var ElementFactory
     */
    private $elementFactory;

    /**
     * Constructor.
     *
     * @param CreateTable $createTable
     * @param DropTable $dropTable
     * @param MigrateDataBetweenShards $migrateDataBetweenShards
     * @param ElementHistoryFactory $elementHistoryFactory
     * @param ElementFactory $elementFactory
     */
    public function __construct(
        CreateTable $createTable,
        DropTable $dropTable,
        MigrateDataBetweenShards $migrateDataBetweenShards,
        ElementHistoryFactory $elementHistoryFactory,
        ElementFactory $elementFactory
    ) {
        $this->createTable = $createTable;
        $this->dropTable = $dropTable;
        $this->migrateDataBetweenShards = $migrateDataBetweenShards;
        $this->elementHistoryFactory = $elementHistoryFactory;
        $this->elementFactory = $elementFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function isOperationDestructive()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationName()
    {
        return self::OPERATION_NAME;
    }

    /**
     * Merge 2 tables: take old data from new table, columns and indexes from old tables:
     * we need to take in account, that 3-rd party extensions can add columns and indexes and also
     * internal constraints in old way: with UpgradeSchema/InstallSchema scripts
     *
     * @param ElementHistory $elementHistory
     * @return Table
     */
    private function getRecreatedTable(ElementHistory $elementHistory) : Table
    {
        /** @var Table $newTable */
        $newTable = $elementHistory->getNew();
        /** @var Table $oldTable */
        $oldTable = $elementHistory->getOld();
        /** @var Table $recreationTable */
        $recreationTable = $this->elementFactory->create(
            'table',
            [
                'name' => $newTable->getName(),
                'type' => 'table',
                'nameWithoutPrefix' => $newTable->getNameWithoutPrefix(),
                'resource' => $newTable->getResource(),
                'engine' => $newTable->getEngine(),
                'charset' => $newTable->getCharset(),
                'collation' => $newTable->getCollation(),
                'onCreate' => $newTable->getOnCreate(),
                'comment' => $newTable->getOnCreate(),
                'columns' => $oldTable->getColumns(),
                'indexes' => $oldTable->getIndexes(),
                'constraints' => array_merge($oldTable->getInternalConstraints(), $newTable->getReferenceConstraints())
            ]
        );

        return $recreationTable;
    }

    /**
     * {@inheritdoc}
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        $recreatedTable = $this->getRecreatedTable($elementHistory);
        $recreatedElementHistory = $this->elementHistoryFactory->create(
            [
                'old' => $elementHistory->getOld(),
                'new' => $recreatedTable
            ]
        );
        $statements = $this->createTable->doOperation($recreatedElementHistory);
        /** @var Statement $statement */
        foreach ($statements as $statement) {
            if ($this->migrateDataBetweenShards->isApplicable((string) $recreatedTable->getOnCreate())) {
                $statement->addTrigger($this->migrateDataBetweenShards->getCallback($recreatedElementHistory));
            }
        }

        return array_merge($statements, $this->dropTable->doOperation($recreatedElementHistory));
    }
}
