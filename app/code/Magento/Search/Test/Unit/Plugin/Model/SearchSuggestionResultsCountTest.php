<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Plugin\Model;

use Magento\Search\Model\QueryResult;
use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Search\Plugin\Model\SearchSuggestionResultsCount;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use PHPUnit\Framework\TestCase;

class SearchSuggestionResultsCountTest extends TestCase
{
    /**
     * @var SearchSuggestionResultsCount
     */
    private SearchSuggestionResultsCount $model;

    /**
     * @var QueryCollectionFactory
     */
    private QueryCollectionFactory $queryCollectionFactoryMock;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManagerMock;

    /**
     * @var QueryResult
     */
    private QueryResult $queryResultMock;

    /**
     * @var Collection
     */
    private Collection $queryCollectionMock;

    /**
     * @var StoreInterface
     */
    private StoreInterface $storeInterfaceMock;

    /**
     * @var AbstractDb
     */
    private AbstractDb $abstractDbMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->queryCollectionFactoryMock = $this->getMockBuilder(QueryCollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryResultMock = $this->getMockBuilder(QueryResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeInterfaceMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $this->abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMockForAbstractClass();
        $this->model = new SearchSuggestionResultsCount(
            $this->queryCollectionFactoryMock,
            $this->storeManagerMock
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testAfterGetResultsCount()
    {
        $expectedArr = ['num_results' => 5];
        $this->queryCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->queryCollectionMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeInterfaceMock);
        $this->storeInterfaceMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->queryCollectionMock->expects($this->once())
            ->method('setStoreId')
            ->willReturnSelf();
        $this->queryResultMock->expects($this->once())
            ->method('getQueryText')
            ->willReturn('mint');
        $this->queryCollectionMock->expects($this->once())
            ->method('setQueryFilter')
            ->willReturn($this->queryCollectionMock);
        $this->abstractDbMock->expects($this->once())
            ->method('getData')
            ->willReturn(['num_results' => 5]);
        $this->queryCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->abstractDbMock]));
        $this->assertEquals(
            $expectedArr,
            $this->model->afterGetResultsCount(
                $this->queryResultMock,
                0
            )
        );
    }
}
