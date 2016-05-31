<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit;

use \Magento\Framework\Data\SearchResultProcessor;

/**
 * Class SearchResultProcessorTest
 */
class SearchResultProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchResultProcessor
     */
    protected $searchResultProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;

    protected function setUp()
    {
        $this->searchCriteriaMock = $this->getMockBuilder('Magento\Framework\Api\CriteriaInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResultCollectionMock = $this->getMockBuilder('Magento\Framework\Data\AbstractSearchResult')
            ->disableOriginalConstructor()
            ->setMethods(['getSearchCriteria', 'getItems', 'getItemId'])
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

        $testItem = new \Magento\Framework\DataObject($itemData);
        $testItem2 = new \Magento\Framework\DataObject($itemData2);

        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$testItem, $testItem2]);

        $this->assertEquals($testItem, $this->searchResultProcessor->getFirstItem());
    }

    public function testGetLastItem()
    {
        $itemData = ['id' => 1];
        $itemData2 = ['id' => 2];

        $testItem = new \Magento\Framework\DataObject($itemData);
        $testItem2 = new \Magento\Framework\DataObject($itemData2);

        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$testItem, $testItem2]);

        $this->assertEquals($testItem2, $this->searchResultProcessor->getLastItem());
    }

    public function testGetAllIds()
    {
        $itemData = ['id' => 1];
        $ids = [1];

        $testItem = new \Magento\Framework\DataObject($itemData);

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

        $testItem = new \Magento\Framework\DataObject($itemData);
        $testItem2 = new \Magento\Framework\DataObject($itemData2);

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

        $testItem = new \Magento\Framework\DataObject($itemData);

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

        $testItem = new \Magento\Framework\DataObject($itemData);
        $testItem2 = new \Magento\Framework\DataObject($itemData2);

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

        $testItem = new \Magento\Framework\DataObject($itemData);
        $testItem2 = new \Magento\Framework\DataObject($itemData2);

        $this->searchResultCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$testItem, $testItem2]);

        $this->assertEquals($testItem2, $this->searchResultProcessor->getItemByColumnValue($columnKey, $columnValue2));
    }
}
