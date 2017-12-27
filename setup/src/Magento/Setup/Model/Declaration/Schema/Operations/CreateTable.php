<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Creates table operation
 */
class CreateTable implements OperationInterface
{
    /**
     * Operation name
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
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param DefinitionAggregator $definitionAggregator
     */
    public function __construct(
        DbSchemaWriterInterface $dbSchemaWriter,
        DefinitionAggregator $definitionAggregator
    ) {
        $this->dbSchemaWriter = $dbSchemaWriter;
        $this->definitionAggregator = $definitionAggregator;
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
    public function doOperation(ElementHistory $elementHistory)
    {
        /** @var Table $table */
        $table = $elementHistory->getNew();

        $definition = [];
        $tableOptions = [
            'resource' => $table->getResource(),
            'name' => $table->getName()
        ];
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
                $definition[$type][$element->getName()] = $this->definitionAggregator->toDefinition($element);
            }
        }

        $this->dbSchemaWriter->createTable($tableOptions, $definition);
    }
}
