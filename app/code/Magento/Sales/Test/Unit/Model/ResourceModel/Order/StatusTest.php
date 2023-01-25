<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order\Status;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /**
     * @var Status
     */
    protected $model;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var Mysql|MockObject
     */
    protected $connectionMock;

    /**
     * @var Select
     */
    protected $selectMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->selectMock = $this->createMock(Select::class);
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where');

        $this->connectionMock = $this->createPartialMock(
            Mysql::class,
            ['update', 'insertOnDuplicate', 'select']
        );
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->selectMock);

        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $tableName = 'sales_order_status_state';
        $this->resourceMock
            ->method('getTableName')
            ->withConsecutive([], [$tableName])
            ->willReturn(null, $tableName);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn(
                $this->connectionMock
            );

        $this->configMock = $this->getMockBuilder(Config::class)
            ->addMethods(['getConnectionName'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Status::class,
            ['resource' => $this->resourceMock]
        );
    }

    /**
     * @return void
     */
    public function testAssignState(): void
    {
        $state = 'processing';
        $status = 'processing';
        $isDefault = 1;
        $visibleOnFront = 1;
        $tableName = 'sales_order_status_state';
        $this->connectionMock->expects($this->once())
            ->method('update')
            ->with(
                $tableName,
                ['is_default' => 0],
                ['state = ?' => $state]
            );
        $this->connectionMock->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                $tableName,
                [
                    'status' => $status,
                    'state' => $state,
                    'is_default' => $isDefault,
                    'visible_on_front' => $visibleOnFront
                ]
            );
        $this->model->assignState($status, $state, $isDefault, $visibleOnFront);
    }
}
