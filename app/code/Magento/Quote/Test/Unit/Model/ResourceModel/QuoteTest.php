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
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

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
        $this->storeMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
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
     * @dataProvider getReservedOrderIdDataProvider
     */
    public function testGetReservedOrderId($entityType, $storeId)
    {
        $this->sequenceManagerMock->expects($this->once())
            ->method('getSequence')
            ->with(\Magento\Sales\Model\Order::ENTITY, $storeId)
            ->willReturn($this->sequenceMock);
        $this->quoteMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->sequenceMock->expects($this->once())
            ->method('getNextValue');

        $this->quote->getReservedOrderId($this->quoteMock);
    }

    /**
     * @return array
     */
    public function getReservedOrderIdDataProvider(): array
    {
        return [
            [\Magento\Sales\Model\Order::ENTITY, 1],
            [\Magento\Sales\Model\Order::ENTITY, 2],
            [\Magento\Sales\Model\Order::ENTITY, 3]
        ];
    }
}