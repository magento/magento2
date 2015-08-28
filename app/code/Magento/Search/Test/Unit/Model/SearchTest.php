<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

class SearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Model\Search
     */
    protected $model;

    /**
     * @var \Magento\Framework\Search\Request\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestBuilder;

    /**
     * @var \Magento\Search\Model\SearchEngine|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchEngine;

    /**
     * @var \Magento\Search\Model\SearchResponseBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResponseBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestBuilder = $this->getMockBuilder('Magento\Framework\Search\Request\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchEngine = $this->getMockBuilder('Magento\Search\Model\SearchEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResponseBuilder = $this->getMockBuilder('Magento\Search\Model\SearchResponseBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject('Magento\Search\Model\Search', [
            'requestBuilder' => $this->requestBuilder,
            'searchEngine' => $this->searchEngine,
            'searchResponseBuilder' => $this->searchResponseBuilder,
            'storeManager' => $this->storeManager,
        ]);
    }

    public function testSearch()
    {
        $requestName = 'requestName';
        $storeId = 333;
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

        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

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
            ->with('scope', $storeId);
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

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $searchResult = $this->model->search($searchCriteria);

        $this->assertInstanceOf('Magento\Framework\Api\Search\SearchResultInterface', $searchResult);
    }
}
