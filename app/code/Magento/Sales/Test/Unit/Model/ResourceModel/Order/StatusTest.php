<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order;

/**
 * Class StatusTest
 *
 * @package Magento\Sales\Model\ResourceModel
 */
class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $this->selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->selectMock->expects($this->any())->method('where');

        $this->connectionMock = $this->getMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['update', 'insertOnDuplicate'],
            [],
            '',
            false
        );
        $this->connectionMock->expects($this->any())->method('select')->will($this->returnValue($this->selectMock));

        $this->resourceMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false
        );
        $tableName = 'sales_order_status_state';
        $this->resourceMock->expects($this->at(1))
            ->method('getTableName')
            ->with($this->equalTo($tableName))
            ->will($this->returnValue($tableName));
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->will(
                $this->returnValue($this->connectionMock)
            );

        $this->configMock = $this->getMock(\Magento\Eav\Model\Config::class, ['getConnectionName'], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Sales\Model\ResourceModel\Order\Status::class,
            ['resource' => $this->resourceMock]
        );
    }

    public function testAssignState()
    {
        $state = 'processing';
        $status = 'processing';
        $isDefault = 1;
        $visibleOnFront = 1;
        $tableName = 'sales_order_status_state';
        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with(
                $this->equalTo($tableName),
                $this->equalTo(['is_default' => 0]),
                $this->equalTo(['state = ?' => $state])
            );
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                $this->equalTo($tableName),
                $this->equalTo(
                    [
                        'status' => $status,
                        'state' => $state,
                        'is_default' => $isDefault,
                        'visible_on_front' => $visibleOnFront,
                    ]
                )
            );
        $this->model->assignState($status, $state, $isDefault, $visibleOnFront);
    }
}
