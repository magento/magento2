<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $scope = 333;
        $filterField = 'filterField';
        $filterValue = 'filterValue';

        $filter = $this->getMockBuilder('Magento\Framework\Api\Filter')
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->once())
            ->method('getField')
            ->willReturn($filterField);
        $filter->expects($this->once())
            ->method('getValue')
            ->willReturn($filterValue);

        $filterGroup = $this->getMockBuilder('Magento\Framework\Api\Search\FilterGroup')
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroup->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filter]);

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
            ->with('scope', $scope);
        $this->requestBuilder->expects($this->any())
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

        $searchResult = $this->model->search($searchCriteria);

        $this->assertInstanceOf('Magento\Framework\Api\Search\SearchResultInterface', $searchResult);
    }
}
