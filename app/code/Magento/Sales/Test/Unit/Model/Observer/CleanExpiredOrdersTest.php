<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
    protected $timeZoneMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Model\Observer\CleanExpiredOrders
     */
    protected $model;

    public function setUp()
    {
        $this->storesConfigMock = $this->getMock(
            '\Magento\Store\Model\StoresConfig',
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock = $this->getMock(
            '\Magento\Sales\Model\Resource\Order\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $this->orderCollectionMock = $this->getMock(
            '\Magento\Sales\Model\Resource\Order\Collection',
            [],
            [],
            '',
            false
        );
        $this->timeZoneMock = $this->getMock(
            '\Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            [],
            [],
            '',
            false
        );

        $this->loggerMock = $this->getMock(
            '\Psr\Log\LoggerInterface',
            [],
            [],
            '',
            false
        );

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            '\Magento\Sales\Model\Observer\CleanExpiredOrders',
            [
                'collectionFactory' => $this->collectionFactoryMock,
                'storesConfig' => $this->storesConfigMock,
                'timeZone' => $this->timeZoneMock,
                'logger' => $this->loggerMock,
            ]
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
        $this->orderCollectionMock->expects($this->exactly(6))->method('addFieldToFilter');
        $this->timeZoneMock->expects($this->exactly(2))->method('getConfigTimezone');
        $this->timeZoneMock->expects($this->exactly(2))->method('date');
        $this->orderCollectionMock->expects($this->exactly(4))->method('walk');
        $this->model->execute();
    }
}
