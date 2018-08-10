<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use \Magento\Framework\View\Layout\Argument\Interpreter\HelperMethod;

class HelperMethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\View\Layout\Argument\Interpreter\NamedParams|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_interpreter;

    /**
     * @var HelperMethod
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_interpreter = $this->getMock(
            'Magento\Framework\View\Layout\Argument\Interpreter\NamedParams',
            [],
            [],
            '',
            false
        );
        $this->_model = new HelperMethod($this->_objectManager, $this->_interpreter);
    }

    public function testEvaluate()
    {
        $input = ['value' => 'some text', 'helper' => __CLASS__ . '::help'];

        $evaluatedValue = ['value' => 'some text (evaluated)'];
        $this->_interpreter->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            $input
        )->will(
            $this->returnValue($evaluatedValue)
        );

        $this->_objectManager->expects($this->once())->method('get')->with(__CLASS__)->will($this->returnValue($this));

        $expected = 'some text (evaluated) (updated)';
        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @param $input
     * @return string
     */
    public function help($input)
    {
        $this->assertSame('some text (evaluated)', $input);
        return $input . ' (updated)';
    }

    /**
     * @param string $helperMethod
     * @param string $expectedExceptionMessage
     *
     * @dataProvider evaluateExceptionDataProvider
     */
    public function testEvaluateException($helperMethod, $expectedExceptionMessage)
    {
        $this->setExpectedException('\InvalidArgumentException', $expectedExceptionMessage);
        $input = ['value' => 'some text', 'helper' => $helperMethod];
        $this->_model->evaluate($input);
    }

    /**
     * @return array
     */
    public function evaluateExceptionDataProvider()
    {
        $nonExistingHelper = __CLASS__ . '::non_existing';
        return [
            'wrong method format' => [
                'help',
                'Helper method name in format "\Class\Name::methodName" is expected',
            ],
            'non-existing method' => [$nonExistingHelper, "Helper method '{$nonExistingHelper}' does not exist"]
        ];
    }
}
