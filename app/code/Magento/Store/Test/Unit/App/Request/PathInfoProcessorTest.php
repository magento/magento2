<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Request;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\PathInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\App\Request\PathInfoProcessor;
use Magento\Store\App\Request\StorePathInfoValidator;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PathInfoProcessorTest extends TestCase
{
    /**
     * @var PathInfoProcessor
     */
    private $model;

    /**
     * @var MockObject
     */
    private $requestMock;

    /**
     * @var MockObject
     */
    private $validatorConfigMock;

    /**
     * @var MockObject
     */
    private $processorConfigMock;

    /**
     * @var MockObject
     */
    private $pathInfoMock;

    /**
     * @var MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var MockObject
     */
    private $storePathInfoValidator;

    /**
     * @var string
     */
    protected $pathInfo = '/storeCode/node_one/';

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validatorConfigMock = $this->getMockForAbstractClass(ReinitableConfigInterface::class);

        $this->processorConfigMock = $this->getMockForAbstractClass(ReinitableConfigInterface::class);

        $this->storeRepositoryMock = $this->getMockForAbstractClass(StoreRepositoryInterface::class);

        $this->pathInfoMock = $this->getMockBuilder(PathInfo ::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storePathInfoValidator = new StorePathInfoValidator(
            $this->validatorConfigMock,
            $this->storeRepositoryMock,
            $this->pathInfoMock
        );

        $this->model = new PathInfoProcessor(
            $this->storePathInfoValidator,
            $this->validatorConfigMock
        );
    }

    public function testProcessIfStoreExistsAndIsNotDirectAccessToFrontName()
    {
        $this->validatorConfigMock->expects($this->any())->method('getValue')->willReturn(true);

        $store = $this->createMock(Store::class);
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
        )->willReturn(
            false
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
