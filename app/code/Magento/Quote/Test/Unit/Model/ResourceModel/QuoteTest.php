<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\ResourceModel;

class QuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \Magento\SalesSequence\Model\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sequenceManagerMock;

    /**
     * @var \Magento\Framework\DB\Sequence\SequenceInterface|\PHPUnit_Framework_MockObject_MockObject
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
        $context = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $snapshot = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityRelationComposite = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequenceManagerMock = $this->getMockBuilder(\Magento\SalesSequence\Model\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequenceMock = $this->getMockBuilder(\Magento\Framework\DB\Sequence\SequenceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote = new \Magento\Quote\Model\ResourceModel\Quote(
            $context,
            $snapshot,
            $entityRelationComposite,
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
    public function getReservedOrderIdDataProvider(): array
    {
        return [
            [\Magento\Sales\Model\Order::ENTITY, 1, '1000000001'],
            [\Magento\Sales\Model\Order::ENTITY, 2, '2000000001'],
            [\Magento\Sales\Model\Order::ENTITY, 3, '3000000001']
        ];
    }
}