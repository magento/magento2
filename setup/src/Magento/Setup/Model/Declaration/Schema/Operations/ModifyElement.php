<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
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
     * Operation name for modify column
     */
    const MODIFY_COLUMN_OPERATION_NAME = 'modify_column';

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
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param AddComplexElement $addElement
     * @param DropElement $dropElement
     */
    public function __construct(
        DbSchemaWriterInterface $dbSchemaWriter,
        AddComplexElement $addElement,
        DropElement $dropElement
    ) {
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
     * As constraints and indexes do not have modify operation, we need to substitute it
     * with remove/create operaions
     *
     * @inheritdoc
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        $statements = $this->dropElement->doOperation($elementHistory);
        return array_merge($statements, $this->addElement->doOperation($elementHistory));
    }
}
