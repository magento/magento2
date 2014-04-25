<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter;

class NamedParamsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_interpreter;

    /**
     * @var NamedParams
     */
    protected $_model;

    protected function setUp()
    {
        $this->_interpreter = $this->getMockForAbstractClass('Magento\Framework\Data\Argument\InterpreterInterface');
        $this->_model = new NamedParams($this->_interpreter);
    }

    public function testEvaluate()
    {
        $input = array(
            'param' => array('param1' => array('value' => 'value 1'), 'param2' => array('value' => 'value 2'))
        );

        $this->_interpreter->expects(
            $this->at(0)
        )->method(
            'evaluate'
        )->with(
            array('value' => 'value 1')
        )->will(
            $this->returnValue('value 1 (evaluated)')
        );
        $this->_interpreter->expects(
            $this->at(1)
        )->method(
            'evaluate'
        )->with(
            array('value' => 'value 2')
        )->will(
            $this->returnValue('value 2 (evaluated)')
        );
        $expected = array('param1' => 'value 1 (evaluated)', 'param2' => 'value 2 (evaluated)');

        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider evaluateWrongParamDataProvider
     */
    public function testEvaluateWrongParam($input, $expectedExceptionMessage)
    {
        $this->setExpectedException('\InvalidArgumentException', $expectedExceptionMessage);
        $this->_model->evaluate($input);
    }

    public function evaluateWrongParamDataProvider()
    {
        return array(
            'root param is non-array' => array(
                array('param' => 'non-array'),
                'Layout argument parameters are expected to be an array'
            ),
            'individual param is non-array' => array(
                array('param' => array('sub-param' => 'non-array')),
                'Parameter data of layout argument is expected to be an array'
            )
        );
    }
}
