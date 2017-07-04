<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Indexer;

class ActiveTableSwitcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher
     */
    private $model;

    protected function setUp()
    {
        $this->model = new \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher();
    }

    public function testSwitch()
    {
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
