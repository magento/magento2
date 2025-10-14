<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Sales\Model\Order;
use Magento\SalesSequence\Model\Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Quote\Model\ResourceModel\Quote.
 */
class QuoteTest extends TestCase
{
    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var Manager|MockObject
     */
    private $sequenceManagerMock;

    /**
     * @var SequenceInterface|MockObject
     */
    private $sequenceMock;

    /**
     * @var QuoteResource
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->sequenceManagerMock = $this->createMock(Manager::class);
        $this->sequenceMock = $this->getMockForAbstractClass(SequenceInterface::class);
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
    public static function getReservedOrderIdDataProvider(): array
    {
        return [
            [Order::ENTITY, 1, '1000000001'],
            [Order::ENTITY, 2, '2000000001'],
            [Order::ENTITY, 3, '3000000001'],
        ];
    }
}
