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
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Integer;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\ElementHistoryFactory;
use Magento\Framework\Setup\Declaration\Schema\OperationInterface;

/**
 * Add column to table operation.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddColumn implements OperationInterface
{
    /**
     * Operation name.
     */
    const OPERATION_NAME = 'add_column';

    /**
     * This key is service key and need only for migration of data on auto_increment field.
     */
    const TEMPORARY_KEY = 'AUTO_INCREMENT_TEMPORARY_KEY';

    /**
     * @var DefinitionAggregator
     */
    private $definitionAggregator;

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
     * @var ElementFactory
     */
    private $elementFactory;

    /**
     * @var ElementHistoryFactory
     */
    private $elementHistoryFactory;

    /**
     * @var AddComplexElement
     */
    private $addComplexElement;

    /**
     * @var DropElement
     */
    private $dropElement;

    /**
     * @var DDLTriggerInterface[]
     */
    private $triggers;

    /**
     * AddColumn constructor.
     *
     * @param DefinitionAggregator $definitionAggregator
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param ElementFactory $elementFactory
     * @param ElementHistoryFactory $elementHistoryFactory
     * @param AddComplexElement $addComplexElement
     * @param DropElement $dropElement
     * @param array $triggers
     */
    public function __construct(
        DefinitionAggregator $definitionAggregator,
        DbSchemaWriterInterface $dbSchemaWriter,
        ElementFactory $elementFactory,
        ElementHistoryFactory $elementHistoryFactory,
        AddComplexElement $addComplexElement,
        DropElement $dropElement,
        array $triggers = []
    ) {
        $this->definitionAggregator = $definitionAggregator;
        $this->dbSchemaWriter = $dbSchemaWriter;
        $this->elementFactory = $elementFactory;
        $this->elementHistoryFactory = $elementHistoryFactory;
        $this->addComplexElement = $addComplexElement;
        $this->dropElement = $dropElement;
        $this->triggers = $triggers;
    }

    /**
     * Creates index history.
     *
     * @param Column $column
     * @return ElementHistory
     */
    private function getTemporaryIndexHistory(Column $column)
    {
        $index = $this->elementFactory->create(
            Index::TYPE,
            [
                'name' => self::TEMPORARY_KEY,
                'column' => [$column->getName()],
                'columns' => [$column],
                'table' => $column->getTable()
            ]
        );
        return $this->elementHistoryFactory->create(['new' => $index]);
    }

    /**
     * @inheritdoc
     */
    public function getOperationName()
    {
        return self::OPERATION_NAME;
    }

    /**
     * @inheritdoc
     */
    public function isOperationDestructive()
    {
        return false;
    }

    /**
     * Check whether column is auto increment or not.
     *
     * @param Column $column
     * @return bool
     */
    private function columnIsAutoIncrement(Column $column)
    {
        return $column instanceof Integer && $column->isIdentity();
    }

    /**
     * Setup triggers if column have onCreate syntax.
     *
     * @param Statement $statement
     * @param ElementHistory $elementHistory
     * @return array
     */
    private function setupTriggersIfExists(Statement $statement, ElementHistory $elementHistory)
    {
        /** @var Column $column */
        $column = $elementHistory->getNew();
        //Add triggers to column
        foreach ($this->triggers as $ddlTrigger) {
            if ($ddlTrigger->isApplicable((string) $column->getOnCreate())) {
                $statement->addTrigger($ddlTrigger->getCallback($elementHistory));
            }
        }
        $statements = [$statement];
        /**
         * If column has triggers, only then we need to create temporary index on it.
         * As triggers means, that we will not enable primary key until all data will be transferred,
         * so column can left without key (as primary key is disabled) and this cause an error.
         */
        if ($this->columnIsAutoIncrement($column) && !empty($statement->getTriggers())) {
            /**
             * We need to create additional index for auto_increment.
             * As we create new field, and for this field we do not have any key/index, that are
             * required by SQL on any auto_increment field.
             * Primary key will be added to the column later, because column is empty at the moment
             * and if the table is not empty we will get error, such as "Duplicate key entry:".
             */
            $indexHistory = $this->getTemporaryIndexHistory($column);
            /** Add index should goes first */
            $statements = array_merge($this->addComplexElement->doOperation($indexHistory), $statements);
            /** Drop index should goes last and in another query */
            $statements = array_merge($statements, $this->dropElement->doOperation($indexHistory));
        }

        return $statements;
    }

    /**
     * @inheritdoc
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        /**
         * @var Column $element
         */
        $element = $elementHistory->getNew();
        $definition = $this->definitionAggregator->toDefinition($element);
        
        $statement = $this->dbSchemaWriter->addElement(
            $element->getName(),
            $element->getTable()->getResource(),
            $element->getTable()->getName(),
            $definition,
            Column::TYPE
        );
        $statements = $this->setupTriggersIfExists($statement, $elementHistory);

        if ($this->columnIsAutoIncrement($element)) {
            /** We need to reset auto_increment as new field should goes from 1 */
            $statements[] = $this->dbSchemaWriter->resetAutoIncrement(
                $element->getTable()->getName(),
                $element->getTable()->getResource()
            );
        }

        return $statements;
    }
}
