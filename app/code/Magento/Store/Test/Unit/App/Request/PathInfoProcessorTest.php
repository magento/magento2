<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\App\Request;

use Magento\Framework\App\Request\Http;
use Magento\Store\App\Request\PathInfoProcessor;
use Magento\Store\App\Request\StorePathInfoValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PathInfoProcessorTest extends TestCase
{
    /**
     * @var StorePathInfoValidator|MockObject
     */
    private $storePathInfoValidatorMock;

    /**
     * @var PathInfoProcessor
     */
    private $model;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var string
     */
    private $storeCode;

    /**
     * @var string
     */
    private $pathInfo;

    protected function setUp(): void
    {
        $this->storePathInfoValidatorMock = $this->createMock(StorePathInfoValidator::class);
        $this->model = new PathInfoProcessor($this->storePathInfoValidatorMock);

        $this->requestMock = $this->createMock(Http::class);
        $this->storeCode = 'storeCode';
        $this->pathInfo = '/' . $this->storeCode . '/node_one/';
    }

    public function testProcessIfStoreIsEmpty(): void
    {
        $this->storePathInfoValidatorMock->expects($this->once())
            ->method('getValidStoreCode')
            ->willReturn(null);

        $pathInfo = $this->model->process($this->requestMock, $this->pathInfo);
        $this->assertEquals($this->pathInfo, $pathInfo);
    }

    public function testProcessIfStoreExistsAndIsNotDirectAccessToFrontName(): void
    {
        $this->storePathInfoValidatorMock->expects($this->once())
            ->method('getValidStoreCode')
            ->willReturn($this->storeCode);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('isDirectAccessFrontendName')
            ->with($this->storeCode)
            ->willReturn(false);

        $pathInfo = $this->model->process($this->requestMock, $this->pathInfo);
        $this->assertEquals('/node_one/', $pathInfo);
    }

    public function testProcessIfStoreExistsAndDirectAccessToFrontName(): void
    {
        $this->storePathInfoValidatorMock->expects($this->once())
            ->method('getValidStoreCode')
            ->willReturn($this->storeCode);
        $this->requestMock->expects($this->atLeastOnce())
            ->method('isDirectAccessFrontendName')
            ->with($this->storeCode)
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('setActionName')
            ->with('noroute');

        $pathInfo = $this->model->process($this->requestMock, $this->pathInfo);
        $this->assertEquals($this->pathInfo, $pathInfo);
    }
}
