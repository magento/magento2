<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Operations;

use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DDL\Triggers\MigrateDataBetweenShards;
use Magento\Framework\Setup\Declaration\Schema\Db\Statement;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
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
     * Constructor.
     *
     * @param CreateTable $createTable
     * @param DropTable $dropTable
     * @param MigrateDataBetweenShards $migrateDataBetweenShards
     */
    public function __construct(
        CreateTable $createTable,
        DropTable $dropTable,
        MigrateDataBetweenShards $migrateDataBetweenShards
    ) {
        $this->createTable = $createTable;
        $this->dropTable = $dropTable;
        $this->migrateDataBetweenShards = $migrateDataBetweenShards;
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
     * {@inheritdoc}
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        /** @var Table $table */
        $table = $elementHistory->getNew();
        $statements = $this->createTable->doOperation($elementHistory);
        /** @var Statement $statement */
        foreach ($statements as $statement) {
            if ($this->migrateDataBetweenShards->isApplicable($table->getOnCreate())) {
                $statement->addTrigger($this->migrateDataBetweenShards->getCallback($elementHistory));
            }
        }

        return array_merge($statements, $this->dropTable->doOperation($elementHistory));
    }
}
