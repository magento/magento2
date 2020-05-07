<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Url\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Url\QueryParamsResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Url\Plugin\RouteParamsResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouteParamsResolverTest extends TestCase
{
    /**
     * @var MockObject|ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject|StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var MockObject|QueryParamsResolverInterface
     */
    protected $queryParamsResolverMock;

    /**
     * @var MockObject|Store
     */
    protected $storeMock;

    /**
     * @var RouteParamsResolver
     */
    protected $model;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock->expects($this->any())->method('getCode')->willReturn('custom_store');

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->queryParamsResolverMock = $this->getMockForAbstractClass(QueryParamsResolverInterface::class);
        $this->model = new RouteParamsResolver(
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
                Store::XML_PATH_STORE_IN_URL,
                ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->willReturn(false);
        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);

        /** @var MockObject $routeParamsResolverMock */
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
                Store::XML_PATH_STORE_IN_URL,
                ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->willReturn(true);

        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);

        /** @var MockObject $routeParamsResolverMock */
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
                Store::XML_PATH_STORE_IN_URL,
                ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->willReturn(false);
        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(true);

        /** @var MockObject $routeParamsResolverMock */
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
                Store::XML_PATH_STORE_IN_URL,
                ScopeInterface::SCOPE_STORE,
                $storeCode
            )
            ->willReturn(true);

        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);

        /** @var MockObject $routeParamsResolverMock */
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
