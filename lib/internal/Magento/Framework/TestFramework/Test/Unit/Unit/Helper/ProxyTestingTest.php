<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\TestFramework\Test\Unit\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ProxyTesting;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class ProxyTestingTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @param string $method
     * @param array $params
     * @param mixed $proxiedResult
     * @param string|null $proxiedMethod
     * @param string|null $proxiedParams
     * @param string $callProxiedMethod
     * @param array $passProxiedParams
     * @param mixed $expectedResult
     */
    #[DataProvider('invokeWithExpectationsDataProvider')]
    public function testInvokeWithExpectations(
        $method,
        $params,
        $proxiedResult,
        $proxiedMethod,
        $proxiedParams,
        $callProxiedMethod,
        $passProxiedParams,
        $expectedResult
    ) {
        // Create proxied object with $callProxiedMethod
        $proxiedObject = $this->createPartialMockWithReflection(
            \stdClass::class,
            [$callProxiedMethod]
        );

        // Create object, which reacts on called $method by calling $callProxiedMethod from proxied object
        $callProxy = function () use ($proxiedObject, $callProxiedMethod, $passProxiedParams) {
            return call_user_func_array([$proxiedObject, $callProxiedMethod], $passProxiedParams);
        };

        $object = $this->createPartialMockWithReflection(
            \stdClass::class,
            [$method]
        );
        $builder = $object->expects($this->once())->method($method);
        call_user_func_array([$builder, 'with'], $params);
        $builder->willReturnCallback($callProxy);

        // Test it
        $helper = new ProxyTesting();
        $result = $helper->invokeWithExpectations(
            $object,
            $proxiedObject,
            $method,
            $params,
            $proxiedResult,
            $proxiedMethod,
            $proxiedParams
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public static function invokeWithExpectationsDataProvider()
    {
        return [
            'all parameters passed' => [
                'method' => 'returnAplusB',
                'params' => [3, 4],
                'proxiedResult' => 7,
                'proxiedMethod' => 'returnAplusB',
                'proxiedParams' => [3, 4],
                'callProxiedMethod' => 'returnAplusB',
                'passProxiedParams' => [3, 4],
                'expectedResult' => 7,
            ],
            'proxied method and params are to be set from proxy method and params' => [
                'method' => 'returnAplusB',
                'params' => [3, 4],
                'proxiedResult' => 7,
                'proxiedMethod' => null,
                'proxiedParams' => null,
                'callProxiedMethod' => 'returnAplusB',
                'passProxiedParams' => [3, 4],
                'expectedResult' => 7,
            ],
            'proxy and proxied method and params differ' => [
                'method' => 'returnAminusBminusC',
                'params' => [10, 1, 2],
                'proxiedResult' => 7,
                'proxiedMethod' => 'returnAminusB',
                'proxiedParams' => [10, 3],
                'callProxiedMethod' => 'returnAminusB',
                'passProxiedParams' => [10, 3],
                'expectedResult' => 7,
            ]
        ];
    }
}
