<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Operations;

use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\OperationInterface;

/**
 * Recreate table operation.
 * Drops and creates table again.
 */
class ReCreateTable implements OperationInterface
{
    /**
     * Operation name.
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
     * Constructor.
     *
     * @param CreateTable $createTable
     * @param DropTable $dropTable
     */
    public function __construct(CreateTable $createTable, DropTable $dropTable)
    {
        $this->createTable = $createTable;
        $this->dropTable = $dropTable;
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
    public function doOperation(ElementHistory $elementHistory)
    {
        $statement = $this->dropTable->doOperation($elementHistory);
        return array_merge($statement, $this->createTable->doOperation($elementHistory));
    }
}
