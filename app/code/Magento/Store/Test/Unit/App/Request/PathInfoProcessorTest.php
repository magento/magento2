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
    private $validatorConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $processorConfigMock;

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

        $this->validatorConfigMock = $this->createMock(\Magento\Framework\App\Config\ReinitableConfigInterface::class);

        $this->processorConfigMock = $this->createMock(\Magento\Framework\App\Config\ReinitableConfigInterface::class);

        $this->storeRepositoryMock = $this->createMock(\Magento\Store\Api\StoreRepositoryInterface::class);

        $this->pathInfoMock = $this->getMockBuilder(\Magento\Framework\App\Request\PathInfo ::class)
            ->disableOriginalConstructor()->getMock();

        $this->storePathInfoValidator = new \Magento\Store\App\Request\StorePathInfoValidator(
            $this->validatorConfigMock,
            $this->storeRepositoryMock,
            $this->pathInfoMock
        );

        $this->model = new \Magento\Store\App\Request\PathInfoProcessor(
            $this->storePathInfoValidator,
            $this->validatorConfigMock
        );
    }

    public function testProcessIfStoreExistsAndIsNotDirectAccessToFrontName()
    {
        $this->validatorConfigMock->expects($this->any())->method('getValue')->willReturn(true);

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeRepositoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'getActiveStoreByCode'
        )->with(
            'storeCode'
        )->willReturn($store);
        $this->requestMock->expects(
            $this->atLeastOnce()
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
        $this->validatorConfigMock->expects($this->atLeastOnce())->method('getValue')->willReturn(true);

        $this->storeRepositoryMock->expects(
            $this->any()
        )->method(
            'getActiveStoreByCode'
        );
        $this->requestMock->expects(
            $this->atLeastOnce()
        )->method(
            'isDirectAccessFrontendName'
        )->with(
            'storeCode'
        )->willReturn(true);
        $this->requestMock->expects($this->once())->method('setActionName')->with('noroute');
        $this->assertEquals($this->pathInfo, $this->model->process($this->requestMock, $this->pathInfo));
    }

    public function testProcessIfStoreIsEmpty()
    {
        $this->validatorConfigMock->expects($this->any())->method('getValue')->willReturn(true);

        $path = '/0/node_one/';
        $this->storeRepositoryMock->expects(
            $this->never()
        )->method(
            'getActiveStoreByCode'
        );
        $this->requestMock->expects(
            $this->never()
        )->method(
            'isDirectAccessFrontendName'
        );
        $this->requestMock->expects($this->never())->method('setActionName');
        $this->assertEquals($path, $this->model->process($this->requestMock, $path));
    }

    public function testProcessIfStoreCodeIsNotExist()
    {
        $this->validatorConfigMock->expects($this->atLeastOnce())->method('getValue')->willReturn(true);

        $this->storeRepositoryMock->expects($this->once())->method('getActiveStoreByCode')->with('storeCode')
            ->willThrowException(new NoSuchEntityException());
        $this->requestMock->expects($this->never())->method('isDirectAccessFrontendName');

        $this->assertEquals($this->pathInfo, $this->model->process($this->requestMock, $this->pathInfo));
    }

    public function testProcessIfStoreUrlNotEnabled()
    {
        $this->validatorConfigMock->expects($this->at(0))->method('getValue')->willReturn(true);

        $this->validatorConfigMock->expects($this->at(1))->method('getValue')->willReturn(true);

        $this->validatorConfigMock->expects($this->at(2))->method('getValue')->willReturn(false);

        $this->storeRepositoryMock->expects($this->once())->method('getActiveStoreByCode')->willReturn(1);

        $this->assertEquals($this->pathInfo, $this->model->process($this->requestMock, $this->pathInfo));
    }
}
