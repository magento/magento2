<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\CronJob;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\CronJob\CleanExpiredOrders;

class CleanExpiredOrdersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storesConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var CleanExpiredOrders
     */
    protected $model;

    protected function setUp()
    {
        $this->storesConfigMock = $this->getMock(
            '\Magento\Store\Model\StoresConfig',
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock = $this->getMock(
            '\Magento\Sales\Model\ResourceModel\Order\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->orderCollectionMock = $this->getMock(
            '\Magento\Sales\Model\ResourceModel\Order\Collection',
            [],
            [],
            '',
            false
        );

        $this->loggerMock = $this->getMock('\Psr\Log\LoggerInterface');

        $this->model = new CleanExpiredOrders(
            $this->storesConfigMock,
            $this->loggerMock,
            $this->collectionFactoryMock
        );
    }

    public function testExecute()
    {
        $schedule = [
            0 => 300,
            1 => 20,
        ];
        $this->storesConfigMock->expects($this->once())
            ->method('getStoresConfigByPath')
            ->with('sales/orders/delete_pending_after')
            ->willReturn($schedule);
        $this->collectionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->orderCollectionMock);
        $this->orderCollectionMock->expects($this->exactly(4))->method('addFieldToFilter');
        $this->orderCollectionMock->expects($this->exactly(4))->method('walk');

        $selectMock = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);
        $selectMock->expects($this->exactly(2))->method('where')->willReturnSelf();
        $this->orderCollectionMock->expects($this->exactly(2))->method('getSelect')->willReturn($selectMock);

        $this->loggerMock->expects($this->never())->method('error');
        $this->model->execute();
    }

    public function testExecuteWithException()
    {
        $schedule = [
            1 => 20,
        ];
        $exceptionMessage = 'Error500';

        $this->storesConfigMock->expects($this->once())
            ->method('getStoresConfigByPath')
            ->with('sales/orders/delete_pending_after')
            ->willReturn($schedule);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderCollectionMock);
        $this->orderCollectionMock->expects($this->exactly(2))->method('addFieldToFilter');

        $selectMock = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);
        $selectMock->expects($this->once())->method('where')->willReturnSelf();
        $this->orderCollectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);

        $this->orderCollectionMock->expects($this->once())
            ->method('walk')
            ->willThrowException(new \Exception($exceptionMessage));
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Error cancelling deprecated orders: ' . $exceptionMessage);

        $this->model->execute();
    }
}
