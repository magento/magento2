<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Modify column in table operation.
 */
class ModifyColumn implements OperationInterface
{
    /**
     * Operation name.
     */
    const OPERATION_NAME = 'modify_column';

    /**
     * @var DefinitionAggregator
     */
    private $definitionAggregator;

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
     * Constructor.
     *
     * @param DefinitionAggregator $definitionAggregator
     * @param DbSchemaWriterInterface $dbSchemaWriter
     */
    public function __construct(
        DefinitionAggregator $definitionAggregator,
        DbSchemaWriterInterface $dbSchemaWriter
    ) {
        $this->definitionAggregator = $definitionAggregator;
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
        /** @var Column $column */
        $column = $elementHistory->getNew();

        $definition = $this->definitionAggregator->toDefinition($column);

        return [$this->dbSchemaWriter->modifyColumn(
            $column->getName(),
            $column->getTable()->getResource(),
            $column->getTable()->getName(),
            $definition
        )];
    }
}
