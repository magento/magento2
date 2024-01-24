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
use Magento\Store\App\Request\StorePathInfoValidator;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\Validation\StoreCodeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StorePathInfoValidatorTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var StoreRepositoryInterface|MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var PathInfo|MockObject
     */
    private $pathInfoMock;

    /**
     * @var StoreCodeValidator|MockObject
     */
    private $storeCodeValidatorMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var StorePathInfoValidator
     */
    private $storePathInfoValidator;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeRepositoryMock = $this->createMock(StoreRepositoryInterface::class);
        $this->pathInfoMock = $this->createMock(PathInfo::class);
        $this->storeCodeValidatorMock = $this->createMock(StoreCodeValidator::class);
        $this->storePathInfoValidator = new StorePathInfoValidator(
            $this->configMock,
            $this->storeRepositoryMock,
            $this->pathInfoMock,
            $this->storeCodeValidatorMock
        );

        $this->requestMock = $this->createMock(Http::class);
        $this->requestMock->method('getRequestUri')
            ->willReturn('/path/');
        $this->requestMock->method('getBaseUrl')
            ->willReturn('example.com');
    }

    public function testGetValidStoreCodeWithoutStoreInUrl(): void
    {
        $this->pathInfoMock->method('getPathInfo')
            ->willReturn('/a/b/');
        $this->storeCodeValidatorMock->method('isValid')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Store::XML_PATH_STORE_IN_URL)
            ->willReturn(false);
        $this->storeRepositoryMock->expects($this->never())
            ->method('getActiveStoreByCode');

        $result = $this->storePathInfoValidator->getValidStoreCode($this->requestMock, '/b/c/');
        $this->assertNull($result);
    }

    public function testGetValidStoreCodeWithoutPathInfo(): void
    {
        $storeCode = 'store1';

        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Store::XML_PATH_STORE_IN_URL)
            ->willReturn(true);
        $this->pathInfoMock->expects($this->once())
            ->method('getPathInfo')
            ->willReturn('/' . $storeCode . '/path1/');
        $this->storeCodeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($storeCode)
            ->willReturn(true);
        $store = $this->createMock(Store::class);
        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->with($storeCode)
            ->willReturn($store);

        $result = $this->storePathInfoValidator->getValidStoreCode($this->requestMock, '');
        $this->assertEquals($storeCode, $result);
    }

    public function testGetValidStoreCodeWithEmptyPathInfo(): void
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(Store::XML_PATH_STORE_IN_URL)
            ->willReturn(true);
        $this->pathInfoMock->expects($this->once())
            ->method('getPathInfo')
            ->willReturn('');
        $this->storeCodeValidatorMock->method('isValid')
            ->willReturn(true);
        $this->storeRepositoryMock->expects($this->never())
            ->method('getActiveStoreByCode');

        $result = $this->storePathInfoValidator->getValidStoreCode($this->requestMock, '');
        $this->assertNull($result);
    }

    /**
     * @dataProvider getValidStoreCodeExceptionDataProvider
     * @param \Throwable $exception
     */
    public function testGetValidStoreCodeThrowsException(\Throwable $exception): void
    {
        $this->configMock->method('getValue')
            ->with(Store::XML_PATH_STORE_IN_URL)
            ->willReturn(true);
        $this->storeCodeValidatorMock->method('isValid')
            ->willReturn(true);

        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->willThrowException($exception);

        $result = $this->storePathInfoValidator->getValidStoreCode($this->requestMock, '/store/');
        $this->assertNull($result);
    }

    public function getValidStoreCodeExceptionDataProvider(): array
    {
        return [
            [new NoSuchEntityException()],
            [new StoreIsInactiveException()],
        ];
    }

    /**
     * @dataProvider getValidStoreCodeDataProvider
     * @param string $pathInfo
     * @param bool $isStoreCodeValid
     * @param string|null $expectedResult
     */
    public function testGetValidStoreCode(string $pathInfo, bool $isStoreCodeValid, ?string $expectedResult): void
    {
        $this->configMock->method('getValue')
            ->with(Store::XML_PATH_STORE_IN_URL)
            ->willReturn(true);
        $this->pathInfoMock->method('getPathInfo')
            ->willReturn('/store2/path2/');
        $this->storeCodeValidatorMock->method('isValid')
            ->willReturn($isStoreCodeValid);
        $store = $this->createMock(Store::class);
        $this->storeRepositoryMock->method('getActiveStoreByCode')
            ->willReturn($store);

        $result = $this->storePathInfoValidator->getValidStoreCode($this->requestMock, $pathInfo);
        $this->assertEquals($expectedResult, $result);
    }

    public function getValidStoreCodeDataProvider(): array
    {
        return [
            ['store1', true, 'store1'],
            ['/store1/path1/', true, 'store1'],
            ['/', true, null],
            ['admin', true, null],
            ['1', false, null],
        ];
    }
}
