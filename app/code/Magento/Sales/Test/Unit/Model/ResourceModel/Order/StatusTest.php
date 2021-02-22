<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order;

/**
 * Class StatusTest
 *
 * @package Magento\Sales\Model\ResourceModel
 */
class StatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $selectMock;

    protected function setUp(): void
    {
        $this->selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where');

        $this->connectionMock = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['update', 'insertOnDuplicate', 'select']
        );
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);

        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $tableName = 'sales_order_status_state';
        $this->resourceMock->expects($this->at(1))
            ->method('getTableName')
            ->with($this->equalTo($tableName))
            ->willReturn($tableName);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn(
                $this->connectionMock
            );

        $this->configMock = $this->createPartialMock(\Magento\Eav\Model\Config::class, ['getConnectionName']);
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
