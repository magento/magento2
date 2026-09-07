<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResult;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\ResponseInterface;
use Magento\Framework\Search\Search;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Framework\Search\SearchResponseBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchTest extends TestCase
{
    /**
     * @var Search
     */
    protected $model;

    /**
     * @var Builder|MockObject
     */
    protected $requestBuilder;

    /**
     * @var SearchEngineInterface|MockObject
     */
    protected $searchEngine;

    /**
     * @var SearchResponseBuilder|MockObject
     */
    protected $searchResponseBuilder;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    protected $scopeResolver;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestBuilder = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchEngine = $this->createMock(SearchEngineInterface::class);

        $this->searchResponseBuilder = $this->getMockBuilder(SearchResponseBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeResolver = $this->createMock(ScopeResolverInterface::class);

        $this->model = $objectManager->getObject(
            Search::class,
            [
                'requestBuilder' => $this->requestBuilder,
                'searchEngine' => $this->searchEngine,
                'searchResponseBuilder' => $this->searchResponseBuilder,
                'scopeResolver' => $this->scopeResolver,
            ]
        );
    }

    public function testSearch()
    {
        $requestName = 'requestName';
        $scopeId = 333;
        $filters = [
            $this->createFilterMock('array_filter', ['arrayValue1', 'arrayValue2']),
            $this->createFilterMock('simple_filter', 'filterValue'),
            $this->createFilterMock('from_filter', ['from' => 30]),
            $this->createFilterMock('to_filter', ['to' => 100]),
            $this->createFilterMock('range_filter', ['from' => 60, 'to' => 82]),
        ];

        $scope = $this->createMock(ScopeInterface::class);

        $filterGroup = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroup->expects($this->once())
            ->method('getFilters')
            ->willReturn($filters);

        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $searchCriteria->expects($this->once())
            ->method('getRequestName')
            ->willReturn($requestName);
        $searchCriteria->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroup]);

        $searchResultMock = $this->createMock(SearchResult::class);
        // SearchResult must support setSearchCriteria() method chaining
        $searchResultMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria)
            ->willReturnSelf();

        $request = $this->createMock(RequestInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $this->requestBuilder->expects($this->once())
            ->method('setRequestName')
            ->with($requestName);
        $this->requestBuilder->expects($this->once())
            ->method('bindDimension')
            ->with('scope', $scopeId);
        $this->requestBuilder->expects($this->exactly(6))
            ->method('bind');
        $this->requestBuilder->expects($this->once())
            ->method('create')
            ->willReturn($request);

        $this->searchEngine->expects($this->once())
            ->method('search')
            ->with($request)
            ->willReturn($response);

        $this->searchResponseBuilder->expects($this->once())
            ->method('build')
            ->with($response)
            ->willReturn($searchResultMock);

        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->willReturn($scope);

        $scope->expects($this->once())
            ->method('getId')
            ->willReturn($scopeId);

        $actualResult = $this->model->search($searchCriteria);

        $this->assertInstanceOf(SearchResultInterface::class, $actualResult);
        $this->assertSame($searchResultMock, $actualResult);
    }

    /**
     * @param $field
     * @param $value
     * @return Filter|MockObject
     */
    private function createFilterMock($field, $value)
    {
        $filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->once())
            ->method('getField')
            ->willReturn($field);
        $filter->expects($this->once())
            ->method('getValue')
            ->willReturn($value);
        return $filter;
    }
}
