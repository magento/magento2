<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Operations;

use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\OperationInterface;

/**
 * Drop table operation.
 */
class DropTable implements OperationInterface
{
    /**
     * Operation name.
     */
    const OPERATION_NAME = 'drop_table';

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
    public function isOperationDestructive()
    {
        return true;
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
    public function doOperation(ElementHistory $tableHistory)
    {
        /**
         * @var Table $table
         */
        $table = $tableHistory->getOld();
        return [$this->dbSchemaWriter->dropTable($table->getName(), $table->getResource())];
    }
}
