<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Grid;

use Magento\Framework\Registry;
use Magento\Sales\Model\Grid\CollectionUpdater;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionUpdaterTest extends TestCase
{
    /**
     * @var CollectionUpdater
     */
    protected $collectionUpdater;

    /**
     * @var MockObject
     */
    protected $registryMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(Registry::class);

        $this->collectionUpdater = new CollectionUpdater(
            $this->registryMock
        );
    }

    public function testUpdateIfOrderNotExists()
    {
        $collectionMock = $this->createMock(
            Collection::class
        );
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
        $collectionMock = $this->createMock(
            Collection::class
        );
        $orderMock = $this->createMock(Order::class);
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
