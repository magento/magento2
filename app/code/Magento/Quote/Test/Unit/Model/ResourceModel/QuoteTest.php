<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\SalesSequence\Model\Manager;

/**
 * Unit test for \Magento\Quote\Model\ResourceModel\Quote.
 */
class QuoteTest extends \PHPUnit\Framework\TestCase
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
     * @var QuoteResource
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->sequenceManagerMock = $this->createMock(Manager::class);
        $this->sequenceMock = $this->createMock(SequenceInterface::class);
        $this->model = $objectManagerHelper->getObject(
            QuoteResource::class,
            [
                'sequenceManager' => $this->sequenceManagerMock,
            ]
        );
    }

    /**
     * @param string $entityType
     * @param int $storeId
     * @param string $reservedOrderId
     * @return void
     * @dataProvider getReservedOrderIdDataProvider
     */
    public function testGetReservedOrderId(string $entityType, int $storeId, string $reservedOrderId): void
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

        $this->assertEquals($reservedOrderId, $this->model->getReservedOrderId($this->quoteMock));
    }

    /**
     * @return array
     */
    public function getReservedOrderIdDataProvider(): array
    {
        return [
            [\Magento\Sales\Model\Order::ENTITY, 1, '1000000001'],
            [\Magento\Sales\Model\Order::ENTITY, 2, '2000000001'],
            [\Magento\Sales\Model\Order::ENTITY, 3, '3000000001'],
        ];
    }
}
