<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote;

class TotalsReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\TotalsReader
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectorMock;


    protected function setUp()
    {
        $this->totalFactoryMock =
            $this->getMock('Magento\Quote\Model\Quote\Address\TotalFactory', ['create'], [], '', false);
        $this->collectionListMock = $this->getMock('Magento\Quote\Model\Quote\TotalsCollectorList', [], [], '', false);
        $this->totalMock = $this->getMock('Magento\Quote\Model\Quote\Address\Total', ['setData'], [], '', false);
        $this->quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->collectorMock =
            $this->getMock('Magento\Quote\Model\Quote\Address\Total\AbstractTotal', [], [], '', false);
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
            $this->getMock('Magento\Quote\Model\Quote\Address\Total', ['setData', 'getCode'], [], '', false);
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
            $this->getMock('Magento\Quote\Model\Quote\Address\Total', ['setData', 'getCode'], [], '', false);
        $secondTotalMock =
            $this->getMock('Magento\Quote\Model\Quote\Address\Total', ['setData', 'getCode'], [], '', false);
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
            $this->getMock('Magento\Quote\Model\Quote\Address\Total', ['setData', 'getCode'], [], '', false);
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
