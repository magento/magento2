<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Setup\Model\Declaration\Schema\Db\AdapterMediator;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
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
     * @var AdapterMediator
     */
    private $adapterMediator;

    /**
     * @param AdapterMediator $adapterMediator
     */
    public function __construct(AdapterMediator $adapterMediator)
    {
        $this->adapterMediator = $adapterMediator;
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
        /** @var Table $oldTable */
        $oldTable = $elementHistory->getOld();
        $this->adapterMediator->dropTable($oldTable);
        $this->adapterMediator->createTable($table);
    }
}
