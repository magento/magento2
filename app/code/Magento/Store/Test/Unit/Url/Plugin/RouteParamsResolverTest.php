<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Url\Plugin;

class RouteParamsResolverTest extends \PHPUnit_Framework_TestCase
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
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->queryParamsResolverMock = $this->getMock('Magento\Framework\Url\QueryParamsResolverInterface');
        $this->model = new \Magento\Store\Url\Plugin\RouteParamsResolver(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->queryParamsResolverMock
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAroundSetRouteParamsScopeInParams()
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
        $routeParamsResolverMock = $this->getMockBuilder('Magento\Framework\Url\RouteParamsResolver')
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->once())->method('setQueryParam')->with('___store', $storeCode);

        $this->model->aroundSetRouteParams(
            $routeParamsResolverMock,
            function ($data, $unsetOldParams) {
                $this->assertArrayNotHasKey('_scope_to_url', $data, 'This data item should have been unset.');
                $this->assertArrayNotHasKey('_scope', $data, 'This data item should have been unset.');
            },
            $data
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAroundSetRouteParamsScopeUseStoreInUrl()
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
        $routeParamsResolverMock = $this->getMockBuilder('Magento\Framework\Url\RouteParamsResolver')
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->never())->method('setQueryParam');

        $this->model->aroundSetRouteParams(
            $routeParamsResolverMock,
            function ($data, $unsetOldParams) {
                $this->assertArrayNotHasKey('_scope_to_url', $data, 'This data item should have been unset.');
                $this->assertArrayNotHasKey('_scope', $data, 'This data item should have been unset.');
            },
            $data
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAroundSetRouteParamsSingleStore()
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
        $routeParamsResolverMock = $this->getMockBuilder('Magento\Framework\Url\RouteParamsResolver')
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->never())->method('setQueryParam');

        $this->model->aroundSetRouteParams(
            $routeParamsResolverMock,
            function ($data, $unsetOldParams) {
                $this->assertArrayNotHasKey('_scope_to_url', $data, 'This data item should have been unset.');
                $this->assertArrayNotHasKey('_scope', $data, 'This data item should have been unset.');
            },
            $data
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAroundSetRouteParamsNoScopeInParams()
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
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->any())->method('getCode')->willReturn($storeCode);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $data = ['_scope_to_url' => true];
        /** @var \PHPUnit_Framework_MockObject_MockObject $routeParamsResolverMock */
        $routeParamsResolverMock = $this->getMockBuilder('Magento\Framework\Url\RouteParamsResolver')
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeParamsResolverMock->expects($this->never())->method('setScope');
        $routeParamsResolverMock->expects($this->once())->method('getScope')->willReturn(false);

        $this->queryParamsResolverMock->expects($this->once())->method('setQueryParam')->with('___store', $storeCode);

        $this->model->aroundSetRouteParams(
            $routeParamsResolverMock,
            function ($data, $unsetOldParams) {
                $this->assertArrayNotHasKey('_scope_to_url', $data, 'This data item should have been unset.');
            },
            $data
        );
    }
}
