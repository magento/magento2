<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit;

use \Magento\Framework\Data\AbstractSearchResult;

/**
 * Class AbstractSearchResultTest
 */
class AbstractSearchResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AbstractSearchResult
     */
    protected $searchResult;

    /**
     * @var \Magento\Framework\DB\QueryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $query;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactory;

    /**
     * @var \Magento\Framework\Api\CriteriaInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteria;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultIteratorMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->criteria = $this->getMockForAbstractClass(\Magento\Framework\Api\CriteriaInterface::class);
        $this->query = $this->getMockForAbstractClass(\Magento\Framework\DB\QueryInterface::class);
        $this->query->expects($this->any())
            ->method('getCriteria')
            ->willReturn($this->criteria);
        $this->entityFactory = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\EntityFactoryInterface::class
        );
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultIteratorMock = $this->getMockBuilder(
            \Magento\Framework\Data\SearchResultIteratorFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->searchResult = $objectManager->getObject(
            \Magento\Framework\Data\Test\Unit\Stub\SearchResult::class,
            [
                'query' => $this->query,
                'entityFactory' => $this->entityFactory,
                'eventManager' => $this->eventManagerMock,
                'resultIteratorFactory' => $this->searchResultIteratorMock
            ]
        );
    }

    public function testGetItems()
    {
        $itemData = ['id' => 1];

        $testItem = new \Magento\Framework\DataObject($itemData);

        $this->query->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$itemData]);
        $this->entityFactory->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\DataObject::class, ['data' => $itemData])
            ->willReturn($testItem);

        $items = $this->searchResult->getItems();

        $this->assertCount(1, $items);
        $this->assertEquals($testItem, end($items));
    }

    public function testGetTotalCount()
    {
        $totalCount = 42;

        $this->query->expects($this->once())
            ->method('getSize')
            ->willReturn($totalCount);

        $this->assertEquals($totalCount, $this->searchResult->getTotalCount());
    }

    public function testGetSearchCriteria()
    {
        $this->assertEquals($this->criteria, $this->searchResult->getSearchCriteria());
    }

    public function testGetSize()
    {
        $size = 42;
        $this->query->expects($this->once())
            ->method('getSize')
            ->willReturn($size);
        $this->assertEquals($size, $this->searchResult->getSize());
    }
}
