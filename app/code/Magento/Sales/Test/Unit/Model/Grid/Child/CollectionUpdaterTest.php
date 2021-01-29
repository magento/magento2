<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Grid\Child;

class CollectionUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Grid\Child\CollectionUpdater
     */
    protected $collectionUpdater;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);

        $this->collectionUpdater = new \Magento\Sales\Model\Grid\Child\CollectionUpdater(
            $this->registryMock
        );
    }

    public function testUpdateIfOrderExists()
    {
        $collectionMock = $this->createMock(
            \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection::class
        );
        $transactionMock = $this->createMock(\Magento\Sales\Model\Order\Payment\Transaction::class);
        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('current_transaction')
            ->willReturn($transactionMock);
        $transactionMock->expects($this->once())->method('getId')->willReturn('transactionId');
        $collectionMock->expects($this->once())->method('addParentIdFilter')->willReturnSelf();
        $this->assertEquals($collectionMock, $this->collectionUpdater->update($collectionMock));
    }
}
