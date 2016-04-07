<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Search
     */
    protected $model;

    /**
     * @var \Magento\Framework\Search\Request\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestBuilder;

    /**
     * @var \Magento\Framework\Search\SearchEngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchEngine;

    /**
     * @var \Magento\Framework\Search\SearchResponseBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResponseBuilder;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeResolver;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestBuilder = $this->getMockBuilder('Magento\Framework\Search\Request\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchEngine = $this->getMockBuilder('Magento\Framework\Search\SearchEngineInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResponseBuilder = $this->getMockBuilder('Magento\Framework\Search\SearchResponseBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeResolver = $this->getMockBuilder('Magento\Framework\App\ScopeResolverInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject('Magento\Framework\Search\Search', [
            'requestBuilder' => $this->requestBuilder,
            'searchEngine' => $this->searchEngine,
            'searchResponseBuilder' => $this->searchResponseBuilder,
            'scopeResolver' => $this->scopeResolver,
        ]);
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

        $scope = $this->getMockBuilder('Magento\Framework\App\ScopeInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        
        $filterGroup = $this->getMockBuilder('Magento\Framework\Api\Search\FilterGroup')
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroup->expects($this->once())
            ->method('getFilters')
            ->willReturn($filters);

        $searchCriteria = $this->getMockBuilder('Magento\Framework\Api\Search\SearchCriteriaInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchCriteria->expects($this->once())
            ->method('getRequestName')
            ->willReturn($requestName);
        $searchCriteria->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroup]);

        $searchResult = $this->getMockBuilder('Magento\Framework\Api\Search\SearchResult')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $response = $this->getMockBuilder('Magento\Framework\Search\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
            ->willReturn($searchResult);

        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->willReturn($scope);

        $scope->expects($this->once())
            ->method('getId')
            ->willReturn($scopeId);

        $searchResult = $this->model->search($searchCriteria);

        $this->assertInstanceOf('Magento\Framework\Api\Search\SearchResultInterface', $searchResult);
    }

    /**
     * @param $field
     * @param $value
     * @return \Magento\Framework\Api\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFilterMock($field, $value)
    {
        $filter = $this->getMockBuilder('Magento\Framework\Api\Filter')
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
