<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Sequence\SequenceInterface;
<<<<<<< HEAD
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Quote\Model\Quote;
use Magento\SalesSequence\Model\Manager;

=======
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\SalesSequence\Model\Manager;

/**
 * Unit test for \Magento\Quote\Model\ResourceModel\Quote.
 */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
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
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );
    }

    /**
<<<<<<< HEAD
     * @param $entityType
     * @param $storeId
     * @param $reservedOrderId
     * @dataProvider getReservedOrderIdDataProvider
     */
    public function testGetReservedOrderId($entityType, $storeId, $reservedOrderId)
=======
     * @param string $entityType
     * @param int $storeId
     * @param string $reservedOrderId
     * @return void
     * @dataProvider getReservedOrderIdDataProvider
     */
    public function testGetReservedOrderId(string $entityType, int $storeId, string $reservedOrderId): void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

<<<<<<< HEAD
        $this->assertEquals($reservedOrderId, $this->quote->getReservedOrderId($this->quoteMock));
=======
        $this->assertEquals($reservedOrderId, $this->model->getReservedOrderId($this->quoteMock));
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @return array
     */
    public function getReservedOrderIdDataProvider(): array
    {
        return [
            [\Magento\Sales\Model\Order::ENTITY, 1, '1000000001'],
            [\Magento\Sales\Model\Order::ENTITY, 2, '2000000001'],
<<<<<<< HEAD
            [\Magento\Sales\Model\Order::ENTITY, 3, '3000000001']
=======
            [\Magento\Sales\Model\Order::ENTITY, 3, '3000000001'],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        ];
    }
}
