<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\App\Request;

use Magento\Framework\Exception\NoSuchEntityException;

class PathInfoProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\App\Request\PathInfoProcessor
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $pathInfoMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var string
     */
    protected $pathInfo = '/storeCode/node_one/';

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);

        $this->configMock = $this->createMock(\Magento\Framework\App\Config\ReinitableConfigInterface::class);

        $this->storeRepositoryMock = $this->createMock(\Magento\Store\Api\StoreRepositoryInterface::class);

        $this->pathInfoMock = $this->getMockBuilder(\Magento\Framework\App\Request\PathInfo::class)
            ->disableOriginalConstructor()->getMock();

        $this->model = new \Magento\Store\App\Request\PathInfoProcessor(
            $this->storeManagerMock,
            $this->configMock,
            $this->storeRepositoryMock,
            $this->pathInfoMock
        );
    }

    public function testProcessIfStoreExistsAndIsNotDirectAccessToFrontName()
    {
        $this->configMock->expects($this->once())->method('getValue')->willReturn(true);

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeRepositoryMock->expects(
            $this->once()
        )->method(
            'getActiveStoreByCode'
        )->with(
            'storeCode'
        )->willReturn($store);
        $this->requestMock->expects(
            $this->once()
        )->method(
            'isDirectAccessFrontendName'
        )->with(
            'storeCode'
        )->will(
            $this->returnValue(false)
        );
        $this->assertEquals('/node_one/', $this->model->process($this->requestMock, $this->pathInfo));
    }

    public function testProcessIfStoreExistsAndDirectAccessToFrontName()
    {
        $this->configMock->expects($this->once())->method('getValue')->willReturn(true);

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeRepositoryMock->expects(
            $this->once()
        )->method(
            'getActiveStoreByCode'
        )->with(
            'storeCode'
        )->willReturn($store);
        $this->requestMock->expects(
            $this->once()
        )->method(
            'isDirectAccessFrontendName'
        )->with(
            'storeCode'
        )->will(
            $this->returnValue(true)
        );
        $this->requestMock->expects($this->once())->method('setActionName')->with('noroute');
        $this->assertEquals($this->pathInfo, $this->model->process($this->requestMock, $this->pathInfo));
    }

    public function testProcessIfStoreIsEmpty()
    {
        $this->configMock->expects($this->once())->method('getValue')->willReturn(true);

        $path = '/0/node_one/';
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeRepositoryMock->expects(
            $this->once()
        )->method(
            'getActiveStoreByCode'
        )->with(
            0
        )->willReturn($store);
        $this->requestMock->expects(
            $this->once()
        )->method(
            'isDirectAccessFrontendName'
        )->with(
            '0'
        )->will(
            $this->returnValue(true)
        );
        $this->requestMock->expects($this->never())->method('setActionName');
        $this->assertEquals($path, $this->model->process($this->requestMock, $path));
    }

    public function testProcessIfStoreCodeIsNotExist()
    {
        $this->configMock->expects($this->once())->method('getValue')->willReturn(true);

        $this->storeRepositoryMock->expects($this->once())->method('getActiveStoreByCode')->with('storeCode')
            ->willThrowException(new NoSuchEntityException());
        $this->requestMock->expects($this->never())->method('isDirectAccessFrontendName');

        $this->assertEquals($this->pathInfo, $this->model->process($this->requestMock, $this->pathInfo));
    }

    public function testProcessIfStoreUrlNotEnabled()
    {
        $this->configMock->expects($this->once())->method('getValue')->willReturn(false);

        $this->storeRepositoryMock->expects($this->never())->method('getActiveStoreByCode');

        $this->assertEquals($this->pathInfo, $this->model->process($this->requestMock, $this->pathInfo));
    }
}
