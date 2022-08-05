<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Grid\Child;

use Magento\Framework\Registry;
use Magento\Sales\Model\Grid\Child\CollectionUpdater;
use Magento\Sales\Model\Order\Payment\Transaction;
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

    public function testUpdateIfOrderExists()
    {
        $collectionMock = $this->createMock(
            Collection::class
        );
        $transactionMock = $this->createMock(Transaction::class);
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
