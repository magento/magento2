<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Drop and create table again
 */
class ReCreateTable implements OperationInterface
{
    /**
     * Operation name
     */
    const OPERATION_NAME = 'recreate_table';

    /**
     * @var CreateTable
     */
    private $createTable;

    /**
     * @var DropTable
     */
    private $dropTable;

    /**
     * @param CreateTable $createTable
     * @param DropTable $dropTable
     */
    public function __construct(CreateTable $createTable, DropTable $dropTable)
    {
        $this->createTable = $createTable;
        $this->dropTable = $dropTable;
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
        $this->dropTable->doOperation($elementHistory);
        $this->createTable->doOperation($elementHistory);
    }
}
