<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\ResourceModel\Fulltext\Collection;

use Magento\Elasticsearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolver;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for SearchCriteriaResolver
 */
class SearchCriteriaResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPageSize', 'create'])
            ->getMock();
    }

    /**
     * @param array|null $orders
     * @param array|null $expected
     * @dataProvider resolveSortOrderDataProvider
     */
    public function testResolve($orders, $expected)
    {
        $searchRequestName = 'test';
        $currentPage = 1;
        $size = 10;

        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRequestName', 'setSortOrders', 'setCurrentPage'])
            ->getMock();
        $searchCriteria->expects($this->once())
            ->method('setRequestName')
            ->with($searchRequestName)
            ->willReturn($searchCriteria);
        $searchCriteria->expects($this->once())
            ->method('setSortOrders')
            ->with($expected)
            ->willReturn($searchCriteria);
        $searchCriteria->expects($this->once())
            ->method('setCurrentPage')
            ->with($currentPage - 1)
            ->willReturn($searchCriteria);

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('setPageSize')
            ->with($size)
            ->willReturn($this->searchCriteriaBuilder);

        $objectManager = new ObjectManagerHelper($this);
        /** @var SearchCriteriaResolver $model */
        $model = $objectManager->getObject(
            SearchCriteriaResolver::class,
            [
                'builder' => $this->searchCriteriaBuilder,
                'searchRequestName' => $searchRequestName,
                'currentPage' => $currentPage,
                'size' => $size,
                'orders' => $orders,
            ]
        );

        $model->resolve();
    }

    /**
     * @return array
     */
    public function resolveSortOrderDataProvider()
    {
        return [
            [
                null,
                null,
            ],
            [
                ['test' => 'ASC'],
                ['test' => 'ASC'],
            ],
        ];
    }
}
