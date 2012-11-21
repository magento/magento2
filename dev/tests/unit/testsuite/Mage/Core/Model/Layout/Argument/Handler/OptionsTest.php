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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Core_Model_Layout_Argument_Handler_Options
 */
class Mage_Core_Model_Layout_Argument_Handler_OptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout_Argument_Handler_Options
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento_ObjectManager_Zend', array('create'), array(), '', false);
        $this->_model = new Mage_Core_Model_Layout_Argument_Handler_Options($this->_objectManagerMock);
    }

    protected function tearDown()
    {
        unset($this->_objectManagerMock);
        unset($this->_model);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testProcessIfOptionModelIncorrect()
    {
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('StdClass')
            ->will($this->returnValue(new StdClass()));
        $this->_model->process('StdClass');
    }

    public function testProcess()
    {
        $optionArray = array('value' => 'LABEL');
        $optionsModel = $this->getMock(
            'Mage_Core_Model_Option_ArrayInterface',
            array(),
            array(),
            'Option_Array_Model',
            false);
        $optionsModel->expects($this->once())->method('toOptionArray')->will($this->returnValue($optionArray));
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Option_Array_Model')
            ->will($this->returnValue($optionsModel));
        $this->assertEquals($optionArray, $this->_model->process('Option_Array_Model'));
    }
}
