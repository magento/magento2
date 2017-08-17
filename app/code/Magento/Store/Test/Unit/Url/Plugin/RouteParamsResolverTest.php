<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Url\Plugin;

class RouteParamsResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Url\QueryParamsResolverInterface
     */
    protected $queryParamsResolverMock;

    /**
     * @var \Magento\Store\Url\Plugin\RouteParamsResolver
     */
    protected $model;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->queryParamsResolverMock = $this->createMock(\Magento\Framework\Url\QueryParamsResolverInterface::class);
        $this->model = new \Magento\Store\Url\Plugin\RouteParamsResolver(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->queryParamsResolverMock
        );
    }

    public function testBeforeSetRouteParamsScopeInParams()
    {
        $storeCode = 'custom_store';
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->will($this->returnValue(false));
        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);
        $data = ['_scope' => $storeCode, '_scope_to_url' => true];
        /** @var \PHPUnit_Framework_MockObject_MockObject $routeParamsResolverMock */
        $routeParamsResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->once())->method('setQueryParam')->with('___store', $storeCode);

        $this->model->beforeSetRouteParams(
            $routeParamsResolverMock,
            $data
        );
    }

    public function testBeforeSetRouteParamsScopeUseStoreInUrl()
    {
        $storeCode = 'custom_store';
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->will($this->returnValue(true));
        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);
        $data = ['_scope' => $storeCode, '_scope_to_url' => true];
        /** @var \PHPUnit_Framework_MockObject_MockObject $routeParamsResolverMock */
        $routeParamsResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->never())->method('setQueryParam');

        $this->model->beforeSetRouteParams(
            $routeParamsResolverMock,
            $data
        );
    }

    public function testBeforeSetRouteParamsSingleStore()
    {
        $storeCode = 'custom_store';
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->will($this->returnValue(false));
        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(true);
        $data = ['_scope' => $storeCode, '_scope_to_url' => true];
        /** @var \PHPUnit_Framework_MockObject_MockObject $routeParamsResolverMock */
        $routeParamsResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->never())->method('setQueryParam');

        $this->model->beforeSetRouteParams(
            $routeParamsResolverMock,
            $data
        );
    }

    public function testBeforeSetRouteParamsNoScopeInParams()
    {
        $storeCode = 'custom_store';
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->will($this->returnValue(false));
        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);
        /** @var \PHPUnit_Framework_MockObject_MockObject| $routeParamsResolverMock */
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->any())->method('getCode')->willReturn($storeCode);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $data = ['_scope_to_url' => true];
        /** @var \PHPUnit_Framework_MockObject_MockObject $routeParamsResolverMock */
        $routeParamsResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->never())->method('setScope');
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn(false);

        $this->queryParamsResolverMock->expects($this->once())->method('setQueryParam')->with('___store', $storeCode);

        $this->model->beforeSetRouteParams(
            $routeParamsResolverMock,
            $data
        );
    }
}
