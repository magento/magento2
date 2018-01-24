<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Modify table operation is used to change table options
 * At this moment it change only table comment
 */
class ModifyTable implements OperationInterface
{
    /**
     * Operation name
     */
    const OPERATION_NAME = 'modify_table';

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
     * @param DbSchemaWriterInterface $dbSchemaWriter
     */
    public function __construct(
        DbSchemaWriterInterface $dbSchemaWriter
    ) {
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
     * @return bool
     */
    public function isOperationDestructive()
    {
        return false;
    }

    /**
     * Modify table column
     *
     * @inheritdoc
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        /** @var Table $table */
        $table = $elementHistory->getNew();
        /** @TODO: add engine change here */
        return [
            $this->dbSchemaWriter->modifyTableOption(
                $table->getName(),
                $table->getResource(),
                'comment',
                $table->getComment()
            )
        ];
    }
}
