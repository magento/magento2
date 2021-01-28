<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Url\Plugin;

class RouteParamsResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Url\QueryParamsResolverInterface
     */
    protected $queryParamsResolverMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Store\Model\Store
     */
    protected $storeMock;

    /**
     * @var \Magento\Store\Url\Plugin\RouteParamsResolver
     */
    protected $model;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock->expects($this->any())->method('getCode')->willReturn('custom_store');

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

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
        $data = ['_scope' => $storeCode, '_scope_to_url' => true];

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->willReturn(false);
        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);

        /** @var \PHPUnit\Framework\MockObject\MockObject $routeParamsResolverMock */
        $routeParamsResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->any())->method('setQueryParam');

        $this->model->beforeSetRouteParams(
            $routeParamsResolverMock,
            $data
        );
    }

    public function testBeforeSetRouteParamsScopeUseStoreInUrl()
    {
        $storeCode = 'custom_store';
        $data = ['_scope' => $storeCode, '_scope_to_url' => true];

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->willReturn(true);

        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);

        /** @var \PHPUnit\Framework\MockObject\MockObject $routeParamsResolverMock */
        $routeParamsResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->never())->method('setQueryParam')->with('___store', $storeCode);

        $this->model->beforeSetRouteParams(
            $routeParamsResolverMock,
            $data
        );
    }

    public function testBeforeSetRouteParamsSingleStore()
    {
        $storeCode = 'custom_store';
        $data = ['_scope' => $storeCode, '_scope_to_url' => true];

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->willReturn(false);
        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(true);

        /** @var \PHPUnit\Framework\MockObject\MockObject $routeParamsResolverMock */
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
        $data = ['_scope_to_url' => true];

        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->willReturn(true);

        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);

        /** @var \PHPUnit\Framework\MockObject\MockObject $routeParamsResolverMock */
        $routeParamsResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->never())->method('setScope');
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn(false);

        $this->queryParamsResolverMock->expects($this->never())->method('setQueryParam')->with('___store', $storeCode);

        $this->model->beforeSetRouteParams(
            $routeParamsResolverMock,
            $data
        );
    }
}
