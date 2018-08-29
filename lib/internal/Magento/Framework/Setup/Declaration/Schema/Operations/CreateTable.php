<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Operations;

use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DDLTriggerInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Framework\Setup\Declaration\Schema\Db\Statement;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraint;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\ElementHistoryFactory;
use Magento\Framework\Setup\Declaration\Schema\OperationInterface;

/**
 * Create table operation.
 */
class CreateTable implements OperationInterface
{
    /**
     * Operation name.
     */
    const OPERATION_NAME = 'create_table';

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
     * @var DefinitionAggregator
     */
    private $definitionAggregator;

    /**
     * @var DDLTriggerInterface[]
     */
    private $columnTriggers;

    /**
     * @var DDLTriggerInterface[]
     */
    private $triggers;

    /**
     * @var ElementHistoryFactory
     */
    private $elementHistoryFactory;

    /**
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param DefinitionAggregator $definitionAggregator
     * @param ElementHistoryFactory $elementHistoryFactory
     * @param array $columnTriggers
     * @param array $triggers
     */
    public function __construct(
        DbSchemaWriterInterface $dbSchemaWriter,
        DefinitionAggregator $definitionAggregator,
        ElementHistoryFactory $elementHistoryFactory,
        array $columnTriggers = [],
        array $triggers = []
    ) {
        $this->dbSchemaWriter = $dbSchemaWriter;
        $this->definitionAggregator = $definitionAggregator;
        $this->columnTriggers = $columnTriggers;
        $this->triggers = $triggers;
        $this->elementHistoryFactory = $elementHistoryFactory;
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
     * Setup callbacks for newely created columns
     *
     * @param array $columns
     * @param Statement $createTableStatement
     * @return void
     */
    private function setupColumnTriggers(array $columns, Statement $createTableStatement)
    {
        foreach ($columns as $column) {
            foreach ($this->columnTriggers as $trigger) {
                if ($trigger->isApplicable((string) $column->getOnCreate())) {
                    $elementHistory = $this->elementHistoryFactory->create([
                        'new' => $column,
                        'old' => $column
                    ]);
                    $createTableStatement->addTrigger(
                        $trigger->getCallback($elementHistory)
                    );
                }
            }
        }
    }

    /**
     * Setup triggers for entire table
     *
     * @param Table $table
     * @param Statement $createTableStatement
     * @return void
     */
    private function setupTableTriggers(Table $table, Statement $createTableStatement)
    {
        foreach ($this->triggers as $trigger) {
            if ($trigger->isApplicable((string) $table->getOnCreate())) {
                $elementHistory = $this->elementHistoryFactory->create([
                    'new' => $table,
                    'old' => $table
                ]);
                $createTableStatement->addTrigger(
                    $trigger->getCallback($elementHistory)
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        /** @var Table $table */
        $table = $elementHistory->getNew();
        $definition = [];
        $data = [
            Column::TYPE => $table->getColumns(),
            Constraint::TYPE => $table->getConstraints(),
            Index::TYPE => $table->getIndexes()
        ];

        foreach ($data as $type => $elements) {
            /**
             * @var ElementInterface $element
             */
            foreach ($elements as $element) {
                //Make definition as flat list.
                $definition[$type . $element->getName()] = $this->definitionAggregator->toDefinition($element);
            }
        }

        $createTableStatement = $this->dbSchemaWriter
            ->createTable(
                $table->getName(),
                $elementHistory->getNew()->getResource(),
                $definition,
                $table->getDiffSensitiveParams()
            );

        $this->setupTableTriggers($table, $createTableStatement);
        $this->setupColumnTriggers($table->getColumns(), $createTableStatement);

        return [$createTableStatement];
    }
}
