<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\Grid;

class CollectionUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Grid\CollectionUpdater
     */
    protected $collectionUpdater;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    protected function setUp()
    {
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);

        $this->collectionUpdater = new \Magento\Sales\Model\Grid\CollectionUpdater(
            $this->registryMock
        );
    }

    public function testUpdateIfOrderNotExists()
    {
        $collectionMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection::class);
        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('current_order')
            ->will($this->returnValue(false));
        $collectionMock->expects($this->never())->method('setOrderFilter');
        $collectionMock
            ->expects($this->once())
            ->method('addOrderInformation')
            ->with(['increment_id'])
            ->will($this->returnSelf());
        $this->assertEquals($collectionMock, $this->collectionUpdater->update($collectionMock));
    }

    public function testUpdateIfOrderExists()
    {
        $collectionMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection::class);
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('current_order')
            ->will($this->returnValue($orderMock));
        $orderMock->expects($this->once())->method('getId')->will($this->returnValue('orderId'));
        $collectionMock->expects($this->once())->method('setOrderFilter')->with('orderId')->will($this->returnSelf());
        $collectionMock
            ->expects($this->once())
            ->method('addOrderInformation')
            ->with(['increment_id'])
            ->will($this->returnSelf());
        $this->assertEquals($collectionMock, $this->collectionUpdater->update($collectionMock));
    }
}
