<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Quote\Model\Quote;
use Magento\SalesSequence\Model\Manager;

class QuoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sequenceManagerMock;

    /**
     * @var SequenceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sequenceMock;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $quote;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $snapshot = $this->getMockBuilder(Snapshot::class)
            ->disableOriginalConstructor()
            ->getMock();
        $relationComposite = $this->getMockBuilder(RelationComposite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequenceManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequenceMock = $this->getMockBuilder(SequenceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote = new \Magento\Quote\Model\ResourceModel\Quote(
            $context,
            $snapshot,
            $relationComposite,
            $this->sequenceManagerMock,
            null
        );
    }

    /**
     * @param $entityType
     * @param $storeId
     * @param $reservedOrderId
     * @dataProvider getReservedOrderIdDataProvider
     */
    public function testGetReservedOrderId($entityType, $storeId, $reservedOrderId)
    {
        $this->sequenceManagerMock->expects($this->once())
            ->method('getSequence')
            ->with($entityType, $storeId)
            ->willReturn($this->sequenceMock);
        $this->quoteMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->sequenceMock->expects($this->once())
            ->method('getNextValue')
            ->willReturn($reservedOrderId);
        $this->assertEquals($reservedOrderId, $this->quote->getReservedOrderId($this->quoteMock));
    }

    /**
     * @return array
     */
    public function getReservedOrderIdDataProvider()
    {
        return [
            [\Magento\Sales\Model\Order::ENTITY, 1, '1000000001'],
            [\Magento\Sales\Model\Order::ENTITY, 2, '2000000001'],
            [\Magento\Sales\Model\Order::ENTITY, 3, '3000000001']
        ];
    }
}
