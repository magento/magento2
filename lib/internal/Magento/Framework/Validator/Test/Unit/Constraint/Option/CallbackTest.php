<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Constraint\Option;

use Magento\Framework\Validator\Constraint\Option\Callback;
use Magento\Framework\Validator\Test\Unit\Test\Callback as TestCallback;
use PHPUnit\Framework\TestCase;

/**
 * Test case for \Magento\Framework\Validator\Constraint\Option\Callback
 */
class CallbackTest extends TestCase
{
    /**
     * Value for test
     */
    const TEST_VALUE = 'test';

    /**
     * Test getValue method
     *
     * @dataProvider getConfigDataProvider
     *
     * @param callable $callback
     * @param mixed $expectedResult
     * @param null $arguments
     * @param bool $createInstance
     */
    public function testGetValue($callback, $expectedResult, $arguments = null, $createInstance = false)
    {
        if (is_array($callback) && is_callable($callback[0])) {
            $callback[0] = $callback[0]($this);
        }
        $option = new Callback($callback, $arguments, $createInstance);
        $this->assertEquals($expectedResult, $option->getValue());
    }

    /**
     * Data provider for testGetValue
     */
    public static function getConfigDataProvider()
    {
        $closure = function () {
            return 'Value from closure';
        };

        return [
            [
                $closure,
                'Value from closure'
            ],
            [
                [
                    static fn (self $testCase) => $testCase->getClassObjectMock()['classObject'],
                    'getTestValue'
                ],
                self::TEST_VALUE
            ],
            [
                [__CLASS__, 'getTestValueStatically'],
                self::TEST_VALUE
            ],
            [
                [
                    static fn (self $testCase) => $testCase->getClassObjectMock()['mock'],
                    'getValue'
                ],
                'Value from mock', ['arg1', 'arg2']
            ],
            [
                [
                    TestCallback::class,
                    'getId'
                ],
                TestCallback::ID,
                null,
                true
            ]
        ];
    }

    public function getClassObjectMock()
    {
        $classObject = $this;
        $mock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getValue'])
            ->getMock();
        $mock->method('getValue')
            ->with('arg1', 'arg2')
            ->willReturn('Value from mock');
        return [
            'classObject' => $classObject,
            'mock' => $mock
        ];
    }

    /**
     * Get TEST_VALUE from static scope
     */
    public static function getTestValueStatically()
    {
        return self::TEST_VALUE;
    }

    /**
     * Get TEST_VALUE
     */
    public function getTestValue()
    {
        return self::TEST_VALUE;
    }

    /**
     * Test setArguments method
     *
     * @dataProvider setArgumentsDataProvider
     *
     * @param string|array $value
     * @param string|array $expectedValue
     */
    public function testSetArguments($value, $expectedValue)
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');
        $option = new Callback(function () {
        });
        $option->setArguments($value);
        $this->assertAttributeEquals($expectedValue, '_arguments', $option);
    }

    /**
     * Data provider for testGetValue
     */
    public static function setArgumentsDataProvider()
    {
        return [
            ['baz', ['baz']],
            [
                ['foo', 'bar'],
                ['foo', 'bar']
            ]
        ];
    }

    /**
     * Test getValue method raises \InvalidArgumentException
     *
     * @dataProvider getValueExceptionDataProvider
     *
     * @param mixed $callback
     * @param string $expectedMessage
     * @param bool $createInstance
     */
    public function testGetValueException($callback, $expectedMessage, $createInstance = false)
    {
        if (is_array($callback)) {
            foreach ($callback as $key => $value) {
                if (is_callable($value)) {
                    $callback[$key] = $value($this);
                }
            }
        } else {
            if (is_callable($callback)) {
                $callback = $callback($this);
            }
        }
        $this->expectException('InvalidArgumentException');
        $option = new Callback($callback, null, $createInstance);
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage($expectedMessage);
        $option->getValue();
    }

    /**
     * Data provider for testGetValueException
     *
     * @return array
     */
    public static function getValueExceptionDataProvider()
    {
        $testObject = static fn (self $testCase) => $testCase->getCallBackTestObject();
        return [
            [
                ['Not_Existing_Callback_Class', 'someMethod'],
                'Class "Not_Existing_Callback_Class" was not found',
            ],
            [
                [$testObject, 'notExistingMethod'],
                'Callback does not callable'
            ],
            [
                ['object' => $testObject, 'method' => 'getTestValue'],
                'Callback does not callable'
            ],
            [
                'unknown_function',
                'Callback does not callable'
            ],
            [
                new \stdClass(),
                'Callback does not callable'
            ],
            [
                [$testObject, 'getTestValue'],
                'Callable expected to be an array with class name as first element',
                true
            ]
        ];
    }

    public function getCallBackTestObject()
    {
        return $this;
    }
}
