<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote;

class TotalsReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\TotalsReader
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $totalFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectionListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $totalMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $collectorMock;

    protected function setUp(): void
    {
        $this->totalFactoryMock =
            $this->createPartialMock(\Magento\Quote\Model\Quote\Address\TotalFactory::class, ['create']);
        $this->collectionListMock = $this->createMock(\Magento\Quote\Model\Quote\TotalsCollectorList::class);
        $this->totalMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Address\Total::class, ['setData']);
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->collectorMock =
            $this->createMock(\Magento\Quote\Model\Quote\Address\Total\AbstractTotal::class);
        $this->model = new \Magento\Quote\Model\Quote\TotalsReader(
            $this->totalFactoryMock,
            $this->collectionListMock
        );
    }

    public function testFetch()
    {
        $total = [];
        $storeId = 1;
        $testedTotalMock =
            $this->createPartialMock(\Magento\Quote\Model\Quote\Address\Total::class, ['setData', 'getCode']);
        $expected = ['my_total_type' => $testedTotalMock];
        $data = ['code' => 'my_total_type'];
        $this->totalMock->expects($this->once())->method('setData')->with([])->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->totalFactoryMock
            ->expects($this->at(0))
            ->method('create')
            ->willReturn($this->totalMock);
        $this->totalFactoryMock->expects($this->at(1))->method('create')->willReturn($testedTotalMock);
        $this->collectionListMock
            ->expects($this->once())
            ->method('getCollectors')
            ->with($storeId)->willReturn([$this->collectorMock]);
        $this->collectorMock
            ->expects($this->once())
            ->method('fetch')
            ->with($this->quoteMock, $this->totalMock)
            ->willReturn($data);
        $testedTotalMock->expects($this->once())->method('setData')->with($data)->willReturnSelf();
        $testedTotalMock->expects($this->any())->method('getCode')->willReturn('my_total_type');
        $this->assertEquals($expected, $this->model->fetch($this->quoteMock, $total));
    }

    public function testFetchWithEmptyData()
    {
        $total = [];
        $storeId = 1;
        $this->totalMock->expects($this->once())->method('setData')->with([])->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->totalFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->totalMock);
        $this->collectionListMock
            ->expects($this->once())
            ->method('getCollectors')
            ->with($storeId)->willReturn([$this->collectorMock]);
        $this->collectorMock
            ->expects($this->once())
            ->method('fetch')
            ->with($this->quoteMock, $this->totalMock)
            ->willReturn([]);
        $this->assertEquals([], $this->model->fetch($this->quoteMock, $total));
    }

    public function testFetchSeveralCollectors()
    {
        $total = [];
        $storeId = 1;
        $firstTotalMock =
            $this->createPartialMock(\Magento\Quote\Model\Quote\Address\Total::class, ['setData', 'getCode']);
        $secondTotalMock =
            $this->createPartialMock(\Magento\Quote\Model\Quote\Address\Total::class, ['setData', 'getCode']);
        $expected = ['first_total_type' => $firstTotalMock, 'second_total_type' => $secondTotalMock];
        $data = [['code' => 'first_total_type'], ['code' => 'second_total_type']];
        $this->totalMock->expects($this->once())->method('setData')->with([])->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->totalFactoryMock
            ->expects($this->at(0))
            ->method('create')
            ->willReturn($this->totalMock);
        $this->totalFactoryMock->expects($this->at(1))->method('create')->willReturn($firstTotalMock);
        $this->totalFactoryMock->expects($this->at(2))->method('create')->willReturn($secondTotalMock);
        $this->collectionListMock
            ->expects($this->once())
            ->method('getCollectors')
            ->with($storeId)->willReturn([$this->collectorMock]);
        $this->collectorMock
            ->expects($this->once())
            ->method('fetch')
            ->with($this->quoteMock, $this->totalMock)
            ->willReturn($data);
        $firstTotalMock->expects($this->once())->method('setData')->with($data[0])->willReturnSelf();
        $secondTotalMock->expects($this->once())->method('setData')->with($data[1])->willReturnSelf();
        $firstTotalMock->expects($this->any())->method('getCode')->willReturn('first_total_type');
        $secondTotalMock->expects($this->any())->method('getCode')->willReturn('second_total_type');
        $this->assertEquals($expected, $this->model->fetch($this->quoteMock, $total));
    }

    public function testConvert()
    {
        $total = [];
        $storeId = 1;
        $testedTotalMock =
            $this->createPartialMock(\Magento\Quote\Model\Quote\Address\Total::class, ['setData', 'getCode']);
        $expected = ['my_total_type' => $testedTotalMock];
        $this->totalMock->expects($this->once())->method('setData')->with([])->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->totalFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->totalMock);
        $this->collectionListMock
            ->expects($this->once())
            ->method('getCollectors')
            ->with($storeId)->willReturn([$this->collectorMock]);
        $this->collectorMock
            ->expects($this->once())
            ->method('fetch')
            ->with($this->quoteMock, $this->totalMock)
            ->willReturn($testedTotalMock);
        $testedTotalMock->expects($this->never())->method('setData');
        $testedTotalMock->expects($this->any())->method('getCode')->willReturn('my_total_type');
        $this->assertEquals($expected, $this->model->fetch($this->quoteMock, $total));
    }
}
