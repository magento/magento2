<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\AdapterMediator;
use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\TableElementInterface;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Drop element operation
 */
class DropElement implements OperationInterface
{
    /**
     * Operation name
     */
    const OPERATION_NAME = 'drop_element';

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
        /**
         * @var TableElementInterface | ElementInterface $element
         */
        $element = $elementHistory->getNew();

        return [
            $this->dbSchemaWriter->dropElement(
                $element->getTable()->getResource(),
                $element->getName(),
                $element->getTable()->getName(),
                $element->getType()
            )
        ];
    }
}
