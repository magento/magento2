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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Core_Model_Layout_Argument_Processor
 */
class Mage_Core_Model_Layout_Argument_ProcessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout_Argument_Processor
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_argumentUpdaterMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_handlerFactory;

    protected function setUp()
    {
        $this->_argumentUpdaterMock = $this->getMock('Mage_Core_Model_Layout_Argument_Updater', array(), array(), '',
            false
        );
        $this->_handlerFactory = $this->getMock('Mage_Core_Model_Layout_Argument_HandlerFactory', array(), array(), '',
            false
        );

        $this->_model = new Mage_Core_Model_Layout_Argument_Processor($this->_argumentUpdaterMock,
            $this->_handlerFactory
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_argumentUpdaterMock);
        unset($this->_handlerFactory);
    }

    /**
     * @param $arguments
     * @dataProvider argumentsDataProvider
     */
    public function testProcess($arguments)
    {
        $argumentHandlerMock = $this->getMock('Mage_Core_Model_Layout_Argument_HandlerInterface', array(), array(), '',
            false
        );
        $argumentHandlerMock->expects($this->once())
            ->method('process')
            ->will($this->returnValue($this->getMock('Dummy_Argument_Value_Class_Name', array(), array(), '', false)));

        $this->_handlerFactory->expects($this->once())->method('getArgumentHandlerByType')
            ->with($this->equalTo('dummy'))
            ->will($this->returnValue($argumentHandlerMock));

        $processedArguments = $this->_model->process($arguments);

        $this->assertArrayHasKey('argKeyOne', $processedArguments);
        $this->assertArrayHasKey('argKeyTwo', $processedArguments);
        $this->assertArrayHasKey('argKeyCorrupted', $processedArguments);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage dummy type handler should implement Mage_Core_Model_Layout_Argument_HandlerInterface
     */
    public function testProcessIfArgumentHandlerFactoryIsIncorrect()
    {
        $this->_handlerFactory->expects($this->once())->method('getArgumentHandlerByType')
            ->with($this->equalTo('dummy'))
            ->will($this->returnValue(new StdClass()));

        $this->_model->process(array('argKey' => array('type' => 'dummy', 'value' => 'incorrect_value')));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage type handler should implement Mage_Core_Model_Layout_Argument_HandlerInterface
     */
    public function testProcessIfArgumentHandlerIsIncorrect()
    {
        $this->_handlerFactory->expects($this->once())->method('getArgumentHandlerByType')
            ->with($this->equalTo('incorrect'))
            ->will($this->returnValue(new StdClass()));

        $this->_model->process(array('argKey' => array('type' => 'incorrect', 'value' => 'incorrect_value')));
    }

    public function argumentsDataProvider()
    {
        return array(
            array(
                array(
                    'argKeyOne' => array('value' => 'argValue'),
                    'argKeyTwo' => array('type' => 'dummy', 'value' => 'Dummy_Argument_Value_Class_Name'),
                    'argKeyCorrupted' => array('no_value' => false)
                )
            )
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider processWhitEmptyArgumentValueAndSpecifiedTypeDataProvider
     */
    public function testProcessWithEmptyArgumentValueAndSpecifiedType($arguments)
    {
        $this->_model->process($arguments);
    }

    public function processWhitEmptyArgumentValueAndSpecifiedTypeDataProvider()
    {
        return array(
            array('argKey' => array('type' => 'Dummy_Type')),
            array('argKey' => array('value' => 0, 'type' => 'Dummy_Type')),
            array('argKey' => array('value' => '', 'type' => 'Dummy_Type')),
            array('argKey' => array('value' => null, 'type' => 'Dummy_Type')),
            array('argKey' => array('value' => false, 'type' => 'Dummy_Type')),
        );
    }

    public function testProcessWithArgumentUpdaters()
    {
        $arguments = array(
            'one' => array(
                'value' => 1,
                'updater' => array('Dummy_Updater_1', 'Dummy_Updater_2')
            )
        );

        $this->_argumentUpdaterMock->expects($this->once())->method('applyUpdaters')->will($this->returnValue(1));

        $expected = array(
            'one' => 1,
        );
        $this->assertEquals($expected, $this->_model->process($arguments));
    }
}
