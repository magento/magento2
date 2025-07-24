<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Controller\Rest\Asynchronous;

use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Validator\EntityArrayValidator\InputArraySizeLimitValue;
use Magento\Webapi\Controller\Rest\InputParamsResolver as WebapiInputParamsResolver;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use Magento\Webapi\Controller\Rest\RequestValidator;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\WebapiAsync\Controller\Rest\Asynchronous\InputParamsResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InputParamsResolverTest extends TestCase
{
    /**
     * @var Request|MockObject
     */
    private $requestMock;

    /**
     * @var ParamsOverrider|MockObject
     */
    private $paramsOverriderMock;

    /**
     * @var ServiceInputProcessor|MockObject
     */
    private $serviceInputProcessorMock;

    /**
     * @var Router|MockObject
     */
    private $routerMock;

    /**
     * @var RequestValidator|MockObject
     */
    private $requestValidatorMock;

    /**
     * @var WebapiInputParamsResolver|MockObject
     */
    private $webapiInputParamsResolverMock;

    /**
     * @var InputArraySizeLimitValue|MockObject
     */
    private $inputArraySizeLimitValueMock;

    /**
     * @var MethodsMap|MockObject
     */
    private $methodsMap;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Request::class);
        $this->paramsOverriderMock = $this->createMock(ParamsOverrider::class);
        $this->serviceInputProcessorMock = $this->createMock(ServiceInputProcessor::class);
        $this->routerMock = $this->createMock(Router::class);
        $this->requestValidatorMock = $this->createMock(RequestValidator::class);
        $this->webapiInputParamsResolverMock = $this->createMock(WebapiInputParamsResolver::class);
        $this->inputArraySizeLimitValueMock = $this->createMock(InputArraySizeLimitValue::class);
        $this->methodsMap = $this->createMock(MethodsMap::class);
    }

    #[DataProvider('requestBodyDataProvider')]
    public function testResolveAsyncBulkShouldThrowAnErrorForInvalidRequestData(
        array $requestData,
        string $expectedExceptionMessage
    ): void {
        $routeMock = $this->createMock(Route::class);
        $routeMock->method('getParameters')
            ->willReturn([]);
        $this->webapiInputParamsResolverMock->method('getRoute')
            ->willReturn($routeMock);
        $this->paramsOverriderMock->method('override')
            ->willReturnArgument(0);
        $this->requestMock->expects($this->once())
            ->method('getRequestData')
            ->willReturn($requestData);
        $this->expectException(\Magento\Framework\Webapi\Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->getModel(true)->resolve();
    }

    public function testResolveAsyncBulk(): void
    {
        $requestData = [['param1' => 'value1'], ['param1' => 'value1']];
        $routeMock = $this->createMock(Route::class);
        $routeMock->method('getServiceClass')
            ->willReturn('serviceClass');
        $routeMock->method('getServiceMethod')
            ->willReturn('serviceMethod');
        $routeMock->method('getParameters')
            ->willReturn([]);
        $this->paramsOverriderMock->method('override')
            ->willReturnArgument(0);
        $this->webapiInputParamsResolverMock->method('getRoute')
            ->willReturn($routeMock);
        $this->requestMock->expects($this->once())
            ->method('getRequestData')
            ->willReturn($requestData);
        $this->serviceInputProcessorMock->expects($this->exactly(2))
            ->method('process')
            ->willReturnArgument(2);
        $this->assertEquals($requestData, $this->getModel(true)->resolve());
    }

    #[DataProvider('requestBodyDataProvider')]
    public function testResolveAsync(array $requestData): void
    {
        $this->webapiInputParamsResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn($requestData);
        $this->requestMock->method('getRequestData')
            ->willReturn($requestData);
        $this->assertEquals([$requestData], $this->getModel(false)->resolve());
    }

    public static function requestBodyDataProvider(): array
    {
        return [
            [[1 => []], 'Request body must be an array'],
            [['0str' => []], 'Request body must be an array'],
            [['str' => []], 'Request body must be an array'],
            [['str' => [], 1 => []], 'Request body must be an array'],
            [['str' => [], 0 => [], 1 => []], 'Request body must be an array'],
        ];
    }

    private function getModel(bool $isBulk = false): InputParamsResolver
    {
        return new InputParamsResolver(
            $this->requestMock,
            $this->paramsOverriderMock,
            $this->serviceInputProcessorMock,
            $this->routerMock,
            $this->requestValidatorMock,
            $this->webapiInputParamsResolverMock,
            $isBulk,
            $this->inputArraySizeLimitValueMock,
            $this->methodsMap
        );
    }
}
