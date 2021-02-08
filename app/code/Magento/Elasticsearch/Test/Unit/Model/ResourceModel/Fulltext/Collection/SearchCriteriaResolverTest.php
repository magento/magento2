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
     * @var SearchCriteriaBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPageSize', 'create'])
            ->getMock();
    }

    /**
     * @param array $params
     * @param array $expected
     * @dataProvider resolveSortOrderDataProvider
     */
    public function testResolve($params, $expected)
    {
        $searchRequestName = 'test';
        $currentPage = 1;
        $size = $params['size'];
        $expectedSize = $expected['size'];
        $orders = $params['orders'];
        $expectedOrders = $expected['orders'];

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
            ->with($expectedOrders)
            ->willReturn($searchCriteria);
        $searchCriteria->expects($this->once())
            ->method('setCurrentPage')
            ->with($currentPage - 1)
            ->willReturn($searchCriteria);

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

        if ($expectedSize === null) {
            $this->searchCriteriaBuilder->expects($this->never())
                ->method('setPageSize');
        } else {
            $this->searchCriteriaBuilder->expects($this->once())
                ->method('setPageSize')
                ->with($expectedSize)
                ->willReturn($this->searchCriteriaBuilder);
        }

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
                ['size' => 0, 'orders' => null],
                ['size' => null, 'orders' => null],
            ],
            [
                ['size' => 10, 'orders' => ['test' => 'ASC']],
                ['size' => null, 'orders' => ['test' => 'ASC']],
            ],
        ];
    }
}
