<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit\Constraint\Option;

/**
 * Test case for \Magento\Framework\Validator\Constraint\Option\Callback
 */
class CallbackTest extends \PHPUnit_Framework_TestCase
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
        $option = new \Magento\Framework\Validator\Constraint\Option\Callback($callback, $arguments, $createInstance);
        $this->assertEquals($expectedResult, $option->getValue());
    }

    /**
     * Data provider for testGetValue
     */
    public function getConfigDataProvider()
    {
        $functionName = create_function('', 'return "Value from function";');
        $closure = function () {
            return 'Value from closure';
        };

        $mock = $this->getMockBuilder('Foo')->setMethods(['getValue'])->getMock();
        $mock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'arg1',
            'arg2'
        )->will(
            $this->returnValue('Value from mock')
        );

        return [
            [$functionName, 'Value from function'],
            [$closure, 'Value from closure'],
            [[$this, 'getTestValue'], self::TEST_VALUE],
            [[__CLASS__, 'getTestValueStatically'], self::TEST_VALUE],
            [[$mock, 'getValue'], 'Value from mock', ['arg1', 'arg2']],
            [
                ['Magento\Framework\Validator\Test\Unit\Test\Callback', 'getId'],
                \Magento\Framework\Validator\Test\Unit\Test\Callback::ID,
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
     * @param mixed $value
     * @param mixed $expectedValue
     */
    public function testSetArguments($value, $expectedValue)
    {
        $option = new \Magento\Framework\Validator\Constraint\Option\Callback(
            function () {
            }
        );
        $option->setArguments($value);
        $this->assertAttributeEquals($expectedValue, '_arguments', $option);
    }

    /**
     * Data provider for testGetValue
     */
    public function setArgumentsDataProvider()
    {
        return [['baz', ['baz']], [['foo', 'bar'], ['foo', 'bar']]];
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
        $option = new \Magento\Framework\Validator\Constraint\Option\Callback($callback, null, $createInstance);
        $this->setExpectedException('InvalidArgumentException', $expectedMessage);
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
            [[$this, 'notExistingMethod'], 'Callback does not callable'],
            [['object' => $this, 'method' => 'getTestValue'], 'Callback does not callable'],
            ['unknown_function', 'Callback does not callable'],
            [new \stdClass(), 'Callback does not callable'],
            [
                [$this, 'getTestValue'],
                'Callable expected to be an array with class name as first element',
                true
            ]
        ];
    }
}
