<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Operations;

use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\OperationInterface;

/**
 * Modify table operation.
 *
 * Used to change table options.
 */
class ModifyTable implements OperationInterface
{
    /**
     * Operation name.
     */
    const OPERATION_NAME = 'modify_table';

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
     * @param DbSchemaWriterInterface $dbSchemaWriter
     */
    public function __construct(DbSchemaWriterInterface $dbSchemaWriter)
    {
        $this->dbSchemaWriter = $dbSchemaWriter;
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
    public function isOperationDestructive()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        /** @var Table $table */
        $table = $elementHistory->getNew();
        /** @var Table $oldTable */
        $oldTable = $elementHistory->getOld();
        $oldOptions = $oldTable->getDiffSensitiveParams();
        $statements = [];

        foreach ($table->getDiffSensitiveParams() as $optionName => $optionValue) {
            if ($oldOptions[$optionName] !== $optionValue) {
                $statements[] = $this->dbSchemaWriter->modifyTableOption(
                    $table->getName(),
                    $table->getResource(),
                    $optionName,
                    $optionValue
                );
            }
        }

        return $statements;
    }
}
