<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class AbstractShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\AbstractShell | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockBuilder(
            '\Magento\Framework\App\AbstractShell'
        )->disableOriginalConstructor()->setMethods(
            ['_applyPhpVariables']
        )->getMockForAbstractClass();
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @param array $arguments
     * @param string $argName
     * @param string $expectedValue
     *
     * @dataProvider setGetArgDataProvider
     */
    public function testSetGetArg($arguments, $argName, $expectedValue)
    {
        $this->_model->setRawArgs($arguments);
        $this->assertEquals($this->_model->getArg($argName), $expectedValue);
    }

    /**
     * @return array
     */
    public function setGetArgDataProvider()
    {
        return [
            'argument with no value' => [
                'arguments' => ['argument', 'argument2'],
                'argName' => 'argument',
                'expectedValue' => true,
            ],
            'dashed argument with value' => [
                'arguments' => ['-argument', 'value'],
                'argName' => 'argument',
                'expectedValue' => 'value',
            ],
            'double-dashed argument with separate value' => [
                'arguments' => ['--argument-name', 'value'],
                'argName' => 'argument-name',
                'expectedValue' => 'value',
            ],
            'double-dashed argument with included value' => [
                'arguments' => ['--argument-name=value'],
                'argName' => 'argument-name',
                'expectedValue' => 'value',
            ],
            'argument with value, then single argument with no value' => [
                'arguments' => ['-argument', 'value', 'argument2'],
                'argName' => 'argument',
                'expectedValue' => 'value',
            ]
        ];
    }
}
