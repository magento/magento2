<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Test\Unit\Helper\TotalTestHelper;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\TotalFactory;
use Magento\Quote\Model\Quote\TotalsCollectorList;
use Magento\Quote\Model\Quote\TotalsReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TotalsReaderTest extends TestCase
{
    /**
     * @var TotalsReader
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $totalFactoryMock;

    /**
     * @var MockObject
     */
    protected $collectionListMock;

    /**
     * @var MockObject
     */
    protected $totalMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    protected $collectorMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->totalFactoryMock =
            $this->createPartialMock(TotalFactory::class, ['create']);
        $this->collectionListMock = $this->createMock(TotalsCollectorList::class);
        $this->totalMock = $this->createPartialMock(Total::class, ['setData']);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->collectorMock =
            $this->createMock(AbstractTotal::class);
        $this->model = new TotalsReader(
            $this->totalFactoryMock,
            $this->collectionListMock
        );
    }

    /**
     * @return void
     */
    public function testFetch(): void
    {
        $total = [];
        $storeId = 1;
        $testedTotalMock = $this->createPartialMock(TotalTestHelper::class, ['setData', 'getCode']);
        $expected = ['my_total_type' => $testedTotalMock];
        $data = ['code' => 'my_total_type'];
        $this->totalMock->expects($this->once())->method('setData')->with([])->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->totalFactoryMock
            ->method('create')
            ->willReturnOnConsecutiveCalls($this->totalMock, $testedTotalMock);
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
        $testedTotalMock->method('getCode')->willReturn('my_total_type');
        $this->assertEquals($expected, $this->model->fetch($this->quoteMock, $total));
    }

    /**
     * @return void
     */
    public function testFetchWithEmptyData(): void
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

    /**
     * @return void
     */
    public function testFetchSeveralCollectors(): void
    {
        $total = [];
        $storeId = 1;
        $firstTotalMock = $this->createPartialMock(TotalTestHelper::class, ['setData', 'getCode']);
        $secondTotalMock = $this->createPartialMock(TotalTestHelper::class, ['setData', 'getCode']);
        $expected = ['first_total_type' => $firstTotalMock, 'second_total_type' => $secondTotalMock];
        $data = [['code' => 'first_total_type'], ['code' => 'second_total_type']];
        $this->totalMock->expects($this->once())->method('setData')->with([])->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->totalFactoryMock
            ->method('create')
            ->willReturnOnConsecutiveCalls($this->totalMock, $firstTotalMock, $secondTotalMock);
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
        $firstTotalMock->method('getCode')->willReturn('first_total_type');
        $secondTotalMock->method('getCode')->willReturn('second_total_type');
        $this->assertEquals($expected, $this->model->fetch($this->quoteMock, $total));
    }

    /**
     * @return void
     */
    public function testConvert(): void
    {
        $total = [];
        $storeId = 1;
        $testedTotalMock = $this->createPartialMock(TotalTestHelper::class, ['setData', 'getCode']);
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
        $testedTotalMock->method('getCode')->willReturn('my_total_type');
        $this->assertEquals($expected, $this->model->fetch($this->quoteMock, $total));
    }
}
