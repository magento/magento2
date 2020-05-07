<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Data\AbstractSearchResult;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\SearchResultIteratorFactory;
use Magento\Framework\Data\Test\Unit\Stub\SearchResult;
use Magento\Framework\DataObject;
use Magento\Framework\DB\QueryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractSearchResultTest extends TestCase
{
    /**
     * @var AbstractSearchResult
     */
    protected $searchResult;

    /**
     * @var QueryInterface|MockObject
     */
    protected $query;

    /**
     * @var EntityFactoryInterface|MockObject
     */
    protected $entityFactory;

    /**
     * @var CriteriaInterface|MockObject
     */
    protected $criteria;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $searchResultIteratorMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->criteria = $this->getMockForAbstractClass(CriteriaInterface::class);
        $this->query = $this->getMockForAbstractClass(QueryInterface::class);
        $this->query->expects($this->any())
            ->method('getCriteria')
            ->willReturn($this->criteria);
        $this->entityFactory = $this->getMockForAbstractClass(
            EntityFactoryInterface::class
        );
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchResultIteratorMock = $this->getMockBuilder(
            SearchResultIteratorFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->searchResult = $objectManager->getObject(
            SearchResult::class,
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

        $testItem = new DataObject($itemData);

        $this->query->expects($this->once())
            ->method('fetchAll')
            ->willReturn([$itemData]);
        $this->entityFactory->expects($this->once())
            ->method('create')
            ->with(DataObject::class, ['data' => $itemData])
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
