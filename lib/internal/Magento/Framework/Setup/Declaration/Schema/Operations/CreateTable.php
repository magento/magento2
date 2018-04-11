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
    private $triggers;

    /**
     * @var ElementHistoryFactory
     */
    private $elementHistoryFactory;

    /**
     * Constructor.
     *
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param DefinitionAggregator $definitionAggregator
     * @param ElementHistoryFactory $elementHistoryFactory
     * @param array $triggers
     */
    public function __construct(
        DbSchemaWriterInterface $dbSchemaWriter,
        DefinitionAggregator $definitionAggregator,
        ElementHistoryFactory $elementHistoryFactory,
        array $triggers = []
    ) {
        $this->dbSchemaWriter = $dbSchemaWriter;
        $this->definitionAggregator = $definitionAggregator;
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
     * In some cases according to backward compatibility we want to use old table,
     * for example in case of table recreation or table renaming
     *
     * We need to use definition of old table in order to prevent removal of 3-rd party columns, indexes, etc..
     * added not with declarative schema
     *
     * @param ElementHistory $elementHistory
     * @return ElementInterface
     */
    private function prepareTable(ElementHistory $elementHistory) : ElementInterface
    {
        return $elementHistory->getOld() ? $elementHistory->getOld() : $elementHistory->getNew();
    }

    /**
     * {@inheritdoc}
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        /** @var Table $table */
        $table = $this->prepareTable($elementHistory);
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
                ['engine' => $table->getEngine(), 'comment' => $table->getComment()]
            );

        //Setup triggers for all column for table.
        foreach ($table->getColumns() as $column) {
            foreach ($this->triggers as $trigger) {
                if ($trigger->isApplicable($column->getOnCreate())) {
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

        return [$createTableStatement];
    }
}
