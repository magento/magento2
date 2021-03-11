<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit\Constraint\Option;

use Magento\Framework\Validator\Constraint\Option\Callback;
use Magento\Framework\Validator\Test\Unit\Test\Callback as TestCallback;

/**
 * Test case for \Magento\Framework\Validator\Constraint\Option\Callback
 */
class CallbackTest extends \PHPUnit\Framework\TestCase
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
        $option = new Callback($callback, $arguments, $createInstance);
        $this->assertEquals($expectedResult, $option->getValue());
    }

    /**
     * Data provider for testGetValue
     */
    public function getConfigDataProvider()
    {
        $closure = function () {
            return 'Value from closure';
        };

        $mock = $this->getMockBuilder('Foo')
            ->setMethods(['getValue'])
            ->getMock();
        $mock->method('getValue')
            ->with('arg1', 'arg2')
            ->willReturn('Value from mock');

        return [
            [
                $closure,
                'Value from closure'
            ],
            [
                [$this, 'getTestValue'],
                self::TEST_VALUE
            ],
            [
                [__CLASS__, 'getTestValueStatically'],
                self::TEST_VALUE
            ],
            [
                [$mock, 'getValue'],
                'Value from mock', ['arg1', 'arg2']
            ],
            [
                [TestCallback::class, 'getId'],
                TestCallback::ID,
                null,
                true
            ]
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
     * @SuppressWarnings(PHPMD)
     */
    public function testSetArguments($value, $expectedValue)
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

        $option = new Callback(function () {
        });
        $option->setArguments($value);
        //$this->assertAttributeEquals($expectedValue, '_arguments', $option);
    }

    /**
     * Data provider for testGetValue
     */
    public function setArgumentsDataProvider()
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
        $this->expectException(\InvalidArgumentException::class);

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
    public function getValueExceptionDataProvider()
    {
        return [
            [
                ['Not_Existing_Callback_Class', 'someMethod'],
                'Class "Not_Existing_Callback_Class" was not found',
            ],
            [
                [$this, 'notExistingMethod'],
                'Callback does not callable'
            ],
            [
                ['object' => $this, 'method' => 'getTestValue'],
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
                [$this, 'getTestValue'],
                'Callable expected to be an array with class name as first element',
                true
            ]
        ];
    }
}
