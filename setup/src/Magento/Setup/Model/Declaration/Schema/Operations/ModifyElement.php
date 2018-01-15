<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Setup\Model\Declaration\Schema\Db\Statement;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
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
     * @var AddComplexElement
     */
    private $addElement;

    /**
     * @var DropElement
     */
    private $dropElement;

    /**
     * @param DefinitionAggregator $definitionAggregator
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param AddComplexElement $addElement
     * @param DropElement $dropElement
     */
    public function __construct(
        DefinitionAggregator $definitionAggregator,
        DbSchemaWriterInterface $dbSchemaWriter,
        AddComplexElement $addElement,
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
     * Modify column definition for existing table
     *
     * @param Column $column
     * @return Statement
     */
    private function modifyColumn(Column $column)
    {
        $definition = $this->definitionAggregator->toDefinition($column);

        return $this->dbSchemaWriter->modifyColumn(
            $column->getName(),
            $column->getTable()->getResource(),
            $column->getTable()->getName(),
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

        if ($element instanceof Constraint || $element instanceof Index) {
            $statements = $this->dropElement->doOperation($elementHistory);
            return array_merge($statements, $this->addElement->doOperation($elementHistory));
        } else {
            /** @var Column $element */
            return [$this->modifyColumn($element)];
        }
    }
}
