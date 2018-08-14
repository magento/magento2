<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    protected $storeMock;

    /**
     * @var \Magento\Store\Url\Plugin\RouteParamsResolver
     */
    protected $model;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock->expects($this->any())->method('getCode')->willReturn('custom_store');

        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->queryParamsResolverMock = $this->getMock(\Magento\Framework\Url\QueryParamsResolverInterface::class);
        $this->model = new \Magento\Store\Url\Plugin\RouteParamsResolver(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->queryParamsResolverMock
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return void
     */
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
            ->will($this->returnValue(false));
        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $routeResolverMock */
        $routeResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->never())->method('setQueryParam');

        $this->model->beforeSetRouteParams(
            $routeResolverMock,
            $data
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return void
     */
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
            ->will($this->returnValue(true));

        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $routeResolverMock */
        $routeResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->once())->method('setQueryParam')->with('___store', $storeCode);

        $this->model->beforeSetRouteParams(
            $routeResolverMock,
            $data
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return void
     */
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
            ->will($this->returnValue(false));
        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(true);

        /** @var \PHPUnit_Framework_MockObject_MockObject $routeResolverMock */
        $routeResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeResolverMock->expects($this->once())->method('setScope')->with($storeCode);
        $routeResolverMock->expects($this->once())->method('getScope')->willReturn($storeCode);

        $this->queryParamsResolverMock->expects($this->never())->method('setQueryParam');

        $this->model->beforeSetRouteParams(
            $routeResolverMock,
            $data
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @return void
     */
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
            ->will($this->returnValue(true));

        $this->storeManagerMock->expects($this->any())->method('hasSingleStore')->willReturn(false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $routeResolverMock */
        $routeResolverMock = $this->getMockBuilder(\Magento\Framework\Url\RouteParamsResolver::class)
            ->setMethods(['setScope', 'getScope'])
            ->disableOriginalConstructor()
            ->getMock();
        $routeResolverMock->expects($this->never())->method('setScope');
        $routeResolverMock->expects($this->once())->method('getScope')->willReturn(false);

        $this->queryParamsResolverMock->expects($this->once())->method('setQueryParam')->with('___store', $storeCode);

        $this->model->beforeSetRouteParams(
            $routeResolverMock,
            $data
        );
    }
}
