<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\CronJob;

use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\CronJob\CleanExpiredOrders;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoresConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CleanExpiredOrdersTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $storesConfigMock;

    /**
     * @var MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var MockObject
     */
    protected $orderCollectionMock;

    /**
     * @var MockObject
     */
    private $orderManagementMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var CleanExpiredOrders
     */
    protected $model;

    protected function setUp(): void
    {
        $this->storesConfigMock = $this->createMock(StoresConfig::class);
        $this->collectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->orderCollectionMock = $this->createMock(Collection::class);
        $this->orderManagementMock = $this->getMockForAbstractClass(OrderManagementInterface::class);

        $this->model = new CleanExpiredOrders(
            $this->storesConfigMock,
            $this->collectionFactoryMock,
            $this->orderManagementMock
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
        $this->orderCollectionMock->expects($this->exactly(2))
            ->method('getAllIds')
            ->willReturn([1, 2]);
        $this->orderCollectionMock->expects($this->exactly(4))->method('addFieldToFilter');
        $this->orderManagementMock->expects($this->exactly(4))->method('cancel');

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->exactly(2))->method('where')->willReturnSelf();
        $this->orderCollectionMock->expects($this->exactly(2))->method('getSelect')->willReturn($selectMock);

        $this->model->execute();
    }

    public function testExecuteWithException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Error500');
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
        $this->orderCollectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1]);
        $this->orderCollectionMock->expects($this->exactly(2))->method('addFieldToFilter');
        $this->orderManagementMock->expects($this->once())->method('cancel');

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())->method('where')->willReturnSelf();
        $this->orderCollectionMock->expects($this->once())->method('getSelect')->willReturn($selectMock);

        $this->orderManagementMock->expects($this->once())
            ->method('cancel')
            ->willThrowException(new \Exception($exceptionMessage));

        $this->model->execute();
    }
}
