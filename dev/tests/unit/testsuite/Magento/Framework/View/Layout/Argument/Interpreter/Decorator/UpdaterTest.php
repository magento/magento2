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
namespace Magento\Framework\View\Layout\Argument\Interpreter\Decorator;

class UpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_interpreter;

    /**
     * @var Updater
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager');
        $this->_interpreter = $this->getMockForAbstractClass('Magento\Framework\Data\Argument\InterpreterInterface');
        $this->_model = new Updater($this->_objectManager, $this->_interpreter);
    }

    public function testEvaluate()
    {
        $input = array(
            'value' => 'some text',
            'updater' => array('Magento\Framework\View\Layout\Argument\UpdaterInterface')
        );
        $evaluatedValue = 'some text (new)';
        $updatedValue = 'some text (updated)';


        $this->_interpreter->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            array('value' => 'some text')
        )->will(
            $this->returnValue($evaluatedValue)
        );

        $updater = $this->getMockForAbstractClass('Magento\Framework\View\Layout\Argument\UpdaterInterface');
        $updater->expects(
            $this->once()
        )->method(
            'update'
        )->with(
            $evaluatedValue
        )->will(
            $this->returnValue($updatedValue)
        );

        $this->_objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Magento\Framework\View\Layout\Argument\UpdaterInterface'
        )->will(
            $this->returnValue($updater)
        );

        $actual = $this->_model->evaluate($input);
        $this->assertSame($updatedValue, $actual);
    }

    public function testEvaluateNoUpdaters()
    {
        $input = array('value' => 'some text');
        $expected = array('value' => 'new text');

        $this->_interpreter->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            $input
        )->will(
            $this->returnValue($expected)
        );
        $this->_objectManager->expects($this->never())->method('get');

        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Layout argument updaters are expected to be an array of classes
     */
    public function testEvaluateWrongUpdaterValue()
    {
        $input = array('value' => 'some text', 'updater' => 'non-array');
        $this->_model->evaluate($input);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Instance of layout argument updater is expected
     */
    public function testEvaluateWrongUpdaterClass()
    {
        $input = array(
            'value' => 'some text',
            'updater' => array(
                'Magento\Framework\View\Layout\Argument\UpdaterInterface',
                'Magento\Framework\ObjectManager'
            )
        );
        $self = $this;
        $this->_objectManager->expects($this->exactly(2))->method('get')->will(
            $this->returnCallback(
                function ($className) use ($self) {
                    return $self->getMockForAbstractClass($className);
                }
            )
        );

        $this->_model->evaluate($input);
    }
}
