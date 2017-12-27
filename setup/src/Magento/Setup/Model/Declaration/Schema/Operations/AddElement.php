<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\TableElementInterface;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Add element to table
 */
class AddElement implements OperationInterface
{
    /**
     * Operation name
     */
    const OPERATION_NAME = 'add_element';

    /**
     * @var DefinitionAggregator
     */
    private $definitionAggregator;

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
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
        /**
         * @var TableElementInterface | ElementInterface $element
         */
        $element = $elementHistory->getNew();
        $elementOptions = [
            'table_name' => $element->getTable()->getName(),
            'element_name' => $element->getName(),
            'resource' => $element->getTable()->getResource()
        ];
        $definition = $this->definitionAggregator->toDefinition($element);

        $this->dbSchemaWriter->addElement(
            $elementOptions,
            $definition,
            $element->getElementType()
        );
    }
}
