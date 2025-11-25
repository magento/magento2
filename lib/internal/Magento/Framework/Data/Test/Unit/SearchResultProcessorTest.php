<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Data\AbstractSearchResult;
use Magento\Framework\Data\SearchResultProcessor;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SearchResultProcessorTest extends TestCase
{
    /**
     * @var SearchResultProcessor
     */
    protected $searchResultProcessor;

    /**
     * @var MockObject
     */
    protected $searchResultCollectionMock;

    /**
     * @var MockObject
     */
    protected $searchCriteriaMock;

    protected function setUp(): void
    {
        $this->searchCriteriaMock = $this->getMockBuilder(CriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->searchResultCollectionMock = $this->getMockBuilder(AbstractSearchResult::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSearchCriteria', 'getItems', 'getItemId'])
            ->getMockForAbstractClass();
        $this->searchResultCollectionMock->expects($this->any())
            ->method('getSearchCriteria')
            ->willReturn($this->searchCriteriaMock);
        $this->searchResultProcessor = new SearchResultProcessor($this->searchResultCollectionMock);
    }

    public function testGetCurrentPage()
    {
        $page = 42;
        $this->searchCriteriaMock->expects($this->once())
            ->method('getLimit')
            ->willReturn([$page]);
        $this->assertEquals($page, $this->searchResultProcessor->getCurrentPage());
    }

    public function testGetPageSize()
    {
        $size = 42;
        $this->searchCriteriaMock->expects($this->once())
            ->method('getLimit')
            ->willReturn([null, $size]);
        $this->assertEquals($size, $this->searchResultProcessor->getPageSize());
    }

    public function testGetFirstItem()
    {
        $itemData = ['id' => 1];
        $itemData2 = ['id' => 2];

        $testItem = new DataObject($itemData);
        $testItem2 = new DataObject($itemData2);

        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$testItem, $testItem2]);

        $this->assertEquals($testItem, $this->searchResultProcessor->getFirstItem());
    }

    public function testGetLastItem()
    {
        $itemData = ['id' => 1];
        $itemData2 = ['id' => 2];

        $testItem = new DataObject($itemData);
        $testItem2 = new DataObject($itemData2);

        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$testItem, $testItem2]);

        $this->assertEquals($testItem2, $this->searchResultProcessor->getLastItem());
    }

    public function testGetAllIds()
    {
        $itemData = ['id' => 1];
        $ids = [1];

        $testItem = new DataObject($itemData);

        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$testItem]);
        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItemId')
            ->with($testItem)
            ->willReturn(1);

        $this->assertEquals($ids, $this->searchResultProcessor->getAllIds());
    }

    public function testGetItemById()
    {
        $itemData = ['id' => 1];
        $itemData2 = ['id' => 2];

        $testItem = new DataObject($itemData);
        $testItem2 = new DataObject($itemData2);

        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([1 => $testItem, $testItem2]);

        $this->assertEquals($testItem2, $this->searchResultProcessor->getItemById(2));
    }

    public function testGetColumnValues()
    {
        $columnKey = 'columnKey';
        $columnValue = 'columnValue';
        $itemData = ['id' => 1, $columnKey => $columnValue];

        $testItem = new DataObject($itemData);

        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$testItem]);
        $this->assertEquals([$columnValue], $this->searchResultProcessor->getColumnValues($columnKey));
    }

    public function testGetItemsByColumnValue()
    {
        $columnKey = 'columnKey';
        $columnValue = 'columnValue';
        $itemData = ['id' => 1, $columnKey => $columnValue];
        $itemData2 = ['id' => 2, $columnKey => $columnValue];

        $testItem = new DataObject($itemData);
        $testItem2 = new DataObject($itemData2);

        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$testItem, $testItem2]);

        $this->assertEquals(
            [$testItem, $testItem2],
            $this->searchResultProcessor->getItemsByColumnValue($columnKey, $columnValue)
        );
    }

    public function testGetItemByColumnValue()
    {
        $columnKey = 'columnKey';
        $columnValue = 'columnValue';
        $columnValue2 = 'columnValue2';
        $itemData = ['id' => 1, $columnKey => $columnValue];
        $itemData2 = ['id' => 2, $columnKey => $columnValue2];

        $testItem = new DataObject($itemData);
        $testItem2 = new DataObject($itemData2);

        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$testItem, $testItem2]);

        $this->assertEquals($testItem2, $this->searchResultProcessor->getItemByColumnValue($columnKey, $columnValue2));
    }
}
