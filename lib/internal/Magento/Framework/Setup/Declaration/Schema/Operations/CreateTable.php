<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Operations;

use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DDLTriggerInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraint;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
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
    private $triggers;

    /**
     * Constructor.
     *
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param DefinitionAggregator $definitionAggregator
     * @param array $triggers
     */
    public function __construct(
        DbSchemaWriterInterface $dbSchemaWriter,
        DefinitionAggregator $definitionAggregator,
        array $triggers = []
    ) {
        $this->dbSchemaWriter = $dbSchemaWriter;
        $this->definitionAggregator = $definitionAggregator;
        $this->triggers = $triggers;
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
                $table->getResource(),
                $definition,
                ['engine' => $table->getEngine(), 'comment' => $table->getComment()]
            );

        //Setup triggers for all column for table.
        foreach ($table->getColumns() as $column) {
            foreach ($this->triggers as $trigger) {
                if ($trigger->isApplicable($column->getOnCreate())) {
                    $createTableStatement->addTrigger(
                        $trigger->getCallback($column)
                    );
                }
            }
        }

        return [$createTableStatement];
    }
}
