<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Indexer;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;

class ActiveTableSwitcherTest extends TestCase
{
    /**
     * @var ActiveTableSwitcher
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new ActiveTableSwitcher();
    }

    public function testSwitch()
    {
        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $tableName = 'tableName';

        $connectionMock->expects($this->once())->method('renameTablesBatch')->with(
            [
                [
                    'oldName' => 'tableName',
                    'newName' => 'tableName_outdated'
                ],
                [
                    'oldName' => 'tableName_replica',
                    'newName' => 'tableName'
                ],
                [
                    'oldName' => 'tableName_outdated',
                    'newName' => 'tableName_replica'
                ],
            ]
        );

        $this->model->switchTable($connectionMock, [$tableName]);
    }

    public function testGetAdditionalTableName()
    {
        $tableName = 'table_name';
        $this->assertEquals(
            $tableName . '_replica',
            $this->model->getAdditionalTableName($tableName)
        );
    }
}
