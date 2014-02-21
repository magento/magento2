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

namespace Magento\Data\Argument\Interpreter;

class ArrayTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Data\Argument\InterpreterInterface
     */
    protected $_interpreter;

    /**
     * @var ArrayType
     */
    protected $_model;

    protected function setUp()
    {
        $this->_interpreter = $this->getMockForAbstractClass('Magento\Data\Argument\InterpreterInterface');
        $this->_model = new ArrayType($this->_interpreter);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Array items are expected
     *
     * @dataProvider evaluateExceptionDataProvider
     */
    public function testEvaluateException($inputData)
    {
        $this->_model->evaluate($inputData);
    }

    public function evaluateExceptionDataProvider()
    {
        return array(
            'no item' => array(array()),
            'non-array item' => array(array('item' => 'non-array')),
        );
    }

    public function testEvaluate()
    {
        $this->_interpreter->expects($this->any())
            ->method('evaluate')
            ->will($this->returnCallback(function ($input) {
                return '-' . $input['value'] . '-';
            }));
        $input = array(array('value' => 'value 1'), array('value' => 'value 2'), array('value' => 'value 3'));
        $expected = array('-value 1-', '-value 2-', '-value 3-');
        $actual = $this->_model->evaluate(array('item' => $input));
        $this->assertSame($expected, $actual);
    }
}
