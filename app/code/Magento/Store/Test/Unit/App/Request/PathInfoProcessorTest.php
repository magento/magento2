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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storePathInfoValidator;

    /**
     * @var string
     */
    protected $pathInfo = '/storeCode/node_one/';

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();

        $this->configMock = $this->createMock(\Magento\Framework\App\Config\ReinitableConfigInterface::class);

        $this->storeRepositoryMock = $this->createMock(\Magento\Store\Api\StoreRepositoryInterface::class);

        $this->pathInfoMock = $this->getMockBuilder(\Magento\Framework\App\Request\PathInfo ::class)
            ->disableOriginalConstructor()->getMock();

        $this->storePathInfoValidator = new \Magento\Store\App\Request\StorePathInfoValidator(
            $this->configMock,
            $this->storeRepositoryMock,
            $this->pathInfoMock
        );

        $this->model = new \Magento\Store\App\Request\PathInfoProcessor(
            $this->storePathInfoValidator
        );
    }

    public function testProcessIfStoreExistsAndIsNotDirectAccessToFrontName()
    {
        $this->configMock->expects($this->exactly(2))->method('getValue')->willReturn(true);

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
        $this->configMock->expects($this->never())->method('getValue');

        $this->storeRepositoryMock->expects(
            $this->never()
        )->method(
            'getActiveStoreByCode'
        );
        $this->requestMock->expects(
            $this->once()
        )->method(
            'isDirectAccessFrontendName'
        )->with(
            'storeCode'
        )->will(
            $this->returnValue(true)
        );
        $this->requestMock->expects($this->never())->method('setActionName')->with('noroute');
        $this->assertEquals($this->pathInfo, $this->model->process($this->requestMock, $this->pathInfo));
    }

    public function testProcessIfStoreIsEmpty()
    {
        $this->configMock->expects($this->never())->method('getValue');

        $path = '/0/node_one/';
        $this->storeRepositoryMock->expects(
            $this->never()
        )->method(
            'getActiveStoreByCode'
        );
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
        $this->requestMock->expects($this->once())->method('isDirectAccessFrontendName')
            ->with('storeCode')
            ->will($this->returnValue(false));

        $this->assertEquals($this->pathInfo, $this->model->process($this->requestMock, $this->pathInfo));
    }

    public function testProcessIfStoreUrlNotEnabled()
    {
        $this->configMock->expects($this->at(0))->method('getValue')->willReturn(true);

        $this->configMock->expects($this->at(1))->method('getValue')->willReturn(false);

        $this->storeRepositoryMock->expects($this->once())->method('getActiveStoreByCode')->willReturn(1);

        $this->assertEquals($this->pathInfo, $this->model->process($this->requestMock, $this->pathInfo));
    }
}
