<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\AdapterMediator;
use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Add element to table
 */
class ModifyElement implements OperationInterface
{
    /**
     * Operation name
     */
    const OPERATION_NAME = 'modify_element';

    /**
     * @var DefinitionAggregator
     */
    private $definitionAggregator;

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
     * @var AddElement
     */
    private $addElement;

    /**
     * @var DropElement
     */
    private $dropElement;

    /**
     * @param DefinitionAggregator $definitionAggregator
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param AddElement $addElement
     * @param DropElement $dropElement
     */
    public function __construct(
        DefinitionAggregator $definitionAggregator,
        DbSchemaWriterInterface $dbSchemaWriter,
        AddElement $addElement,
        DropElement $dropElement
    ) {
        $this->definitionAggregator = $definitionAggregator;
        $this->dbSchemaWriter = $dbSchemaWriter;
        $this->addElement = $addElement;
        $this->dropElement = $dropElement;
    }

    /**
     * @inheritdoc
     */
    public function getOperationName()
    {
        return self::OPERATION_NAME;
    }

    /**
     * Modify constraint (PRIMARY, UNIQUE, FOREIGN keys) definition for existing table
     *
     * @param Constraint $constraint
     */
    private function modifyConstraint(Constraint $constraint)
    {
        $constraintOptions = [
            'table_name' => $constraint->getTable()->getName(),
            'element_name' => $constraint->getName(),
            'resource' => $constraint->getTable()->getResource(),
            'type' => $constraint->getType()
        ];
        $definition = $this->definitionAggregator->toDefinition($constraint);

        $this->dbSchemaWriter->modifyConstraint(
            $constraintOptions,
            $definition
        );
    }


    /**
     * Modify column definition for existing table
     *
     * @param Column $column
     */
    private function modifyColumn(Column $column)
    {
        $columnOptions = [
            'table_name' => $column->getTable()->getName(),
            'element_name' => $column->getName(),
            'resource' => $column->getTable()->getResource()
        ];
        $definition = $this->definitionAggregator->toDefinition($column);

        $this->dbSchemaWriter->modifyColumn(
            $columnOptions,
            $definition
        );
    }

    /**
     * As constraints and indexes do not have modify operation, we need to substitute it
     * with remove/create operaions
     *
     * @inheritdoc
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        $element = $elementHistory->getNew();

        if ($element instanceof Internal) {
            $this->modifyConstraint($element);
        } else if ($element instanceof Index || $element instanceof Reference) {
            $this->dropElement->doOperation($elementHistory);
            $this->addElement->doOperation($elementHistory);
        } else if ($element instanceof Column) {
            $this->modifyColumn($element);
        }
    }
}
