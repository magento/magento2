<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\PathInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\App\Request\PathInfoProcessor;
use Magento\Store\App\Request\StorePathInfoValidator;
use Magento\Store\Model\Store;
use Magento\Store\Model\Validation\StoreCodeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PathInfoProcessorTest extends TestCase
{
    /**
     * @var PathInfoProcessor
     */
    private $model;

    /**
     * @var MockObject|Http
     */
    private $requestMock;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $validatorConfigMock;

    /**
     * @var MockObject|PathInfo
     */
    private $pathInfoMock;

    /**
     * @var MockObject|StoreCodeValidator
     */
    private $storeCodeValidator;

    /**
     * @var MockObject|StoreRepositoryInterface
     */
    private $storeRepositoryMock;

    /**
     * @var StorePathInfoValidator
     */
    private $storePathInfoValidator;

    /**
     * @var string
     */
    private $pathInfo = '/storeCode/node_one/';

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);

        $this->validatorConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeRepositoryMock = $this->createMock(StoreRepositoryInterface::class);
        $this->pathInfoMock = $this->createMock(PathInfo ::class);
        $this->storeCodeValidator = $this->createMock(StoreCodeValidator::class);

        $this->storePathInfoValidator = new StorePathInfoValidator(
            $this->validatorConfigMock,
            $this->storeRepositoryMock,
            $this->pathInfoMock,
            $this->storeCodeValidator
        );
        $this->model = new PathInfoProcessor(
            $this->storePathInfoValidator
        );
    }

    public function testProcessIfStoreExistsAndIsNotDirectAccessToFrontName()
    {
        $this->validatorConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(true);
        $this->storeCodeValidator->expects($this->atLeastOnce())
            ->method('isValid')
            ->willReturn(true);

        $store = $this->createMock(Store::class);
        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->with('storeCode')
            ->willReturn($store);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('isDirectAccessFrontendName')
            ->with('storeCode')
            ->willReturn(false);

        $pathInfo = $this->model->process($this->requestMock, $this->pathInfo);
        $this->assertEquals('/node_one/', $pathInfo);
    }

    public function testProcessIfStoreExistsAndDirectAccessToFrontName()
    {
        $this->validatorConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(true);
        $this->storeCodeValidator->expects($this->atLeastOnce())
            ->method('isValid')
            ->willReturn(true);

        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode');
        $this->requestMock->expects($this->atLeastOnce())
            ->method('isDirectAccessFrontendName')
            ->with('storeCode')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('setActionName')
            ->with('noroute');

        $pathInfo = $this->model->process($this->requestMock, $this->pathInfo);
        $this->assertEquals($this->pathInfo, $pathInfo);
    }

    public function testProcessIfStoreIsEmpty()
    {
        $this->validatorConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(true);
        $this->storeCodeValidator->expects($this->any())
            ->method('isValid')
            ->willReturn(true);

        $path = '/0/node_one/';
        $this->storeRepositoryMock->expects($this->never())
            ->method('getActiveStoreByCode');
        $this->requestMock->expects($this->never())
            ->method('isDirectAccessFrontendName');
        $this->requestMock->expects($this->never())
            ->method('setActionName');

        $pathInfo = $this->model->process($this->requestMock, $path);
        $this->assertEquals($path, $pathInfo);
    }

    public function testProcessIfStoreCodeIsNotExist()
    {
        $this->validatorConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(true);
        $this->storeCodeValidator->expects($this->atLeastOnce())
            ->method('isValid')
            ->willReturn(true);

        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->with('storeCode')
            ->willThrowException(new NoSuchEntityException());
        $this->requestMock->expects($this->never())
            ->method('isDirectAccessFrontendName');

        $pathInfo = $this->model->process($this->requestMock, $this->pathInfo);
        $this->assertEquals($this->pathInfo, $pathInfo);
    }
}
