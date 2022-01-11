<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\QueryResult;
use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class QueryResultTest extends TestCase
{
    /**
     * @var QueryResult
     */
    private QueryResult $model;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var QueryCollectionFactory
     */
    private QueryCollectionFactory $queryCollectionFactoryMock;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManagerMock;

    /**
     * @var StoreInterface
     */
    private StoreInterface $storeInterfaceMock;

    /**
     * @var Collection
     */
    private Collection $queryCollectionMock;

    /**
     * @var QueryResult
     */
    private QueryResult $queryResultMock;

    /**
     * @var AbstractDb
     */
    private AbstractDb $abstractDbMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->queryCollectionFactoryMock = $this->getMockBuilder(QueryCollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeInterfaceMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $this->queryCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryResultMock = $this->getMockBuilder(QueryResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMockForAbstractClass();
        $this->model = new QueryResult(
            'mint',
            'resultsCount',
            $this->queryCollectionFactoryMock,
            $this->storeManagerMock
        );
    }

    /**
     * @dataProvider getPropertiesDataProvider
     */
    public function testGetProperties($queryText)
    {
        /** @var QueryResult $queryResult */
        $queryResult = $this->objectManager->getObject(
            QueryResult::class,
            [
                'queryText' => $queryText
            ]
        );
        $this->assertEquals($queryText, $queryResult->getQueryText());
    }

    /**
     * Data provider for testGetProperties
     * @return array
     */
    public function getPropertiesDataProvider(): array
    {
        return [
            [
                'queryText' => 'Some kind of query text'
            ],
            [
                'queryText' => 'Another query'
            ],
            [
                'queryText' => 'It\' a query too'
            ],
            [
                'queryText' => ''
            ],
            [
                'queryText' => 42
            ],
        ];
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
        $this->queryCollectionMock->expects($this->once())
            ->method('setQueryFilter')
            ->willReturn($this->queryCollectionMock);
        $this->abstractDbMock->expects($this->once())
            ->method('getData')
            ->willReturn(['num_results' => 5]);
        $this->queryCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->abstractDbMock]));
        $this->assertEquals($expectedArr, $this->model->getResultsCount());
    }
}
