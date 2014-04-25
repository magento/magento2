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
namespace Magento\Framework\Data\Argument\Interpreter;

class StringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Argument\Interpreter\String
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_booleanUtils;

    protected function setUp()
    {
        $this->_booleanUtils = $this->getMock('\Magento\Framework\Stdlib\BooleanUtils');
        $this->_booleanUtils->expects(
            $this->any()
        )->method(
            'toBoolean'
        )->will(
            $this->returnValueMap(array(array('true', true), array('false', false)))
        );
        $this->_model = new String($this->_booleanUtils);
        $translateRenderer = $this->getMockForAbstractClass('Magento\Framework\Phrase\RendererInterface');
        $translateRenderer->expects($this->any())->method('render')->will(
            $this->returnCallback(
                function ($input) {
                    return end($input) . ' (translated)';
                }
            )
        );
        \Magento\Framework\Phrase::setRenderer($translateRenderer);
    }

    /**
     * @param array $input
     * @param bool $expected
     *
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate($input, $expected)
    {
        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    public function evaluateDataProvider()
    {
        return array(
            'no value' => array(array(), ''),
            'with value' => array(array('value' => 'some value'), 'some value'),
            'translation required' => array(
                array('value' => 'some value', 'translate' => 'true'),
                'some value (translated)'
            ),
            'translation not required' => array(array('value' => 'some value', 'translate' => 'false'), 'some value')
        );
    }

    /**
     * @param array $input
     *
     * @dataProvider evaluateExceptionDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage String value is expected
     */
    public function testEvaluateException($input)
    {
        $this->_model->evaluate($input);
    }

    public function evaluateExceptionDataProvider()
    {
        return array('not a string' => array(array('value' => 123)));
    }
}
