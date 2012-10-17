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
 * Test class for Mage_Core_Model_Layout_Argument_Handler_Url
 */
class Mage_Core_Model_Layout_Argument_Handler_UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout_Argument_Handler_Url
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlModelMock;


    protected function setUp()
    {
        $this->_objectFactoryMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $this->_urlModelMock = $this->getMock('Mage_Core_Model_Url', array(), array(), '', false);
        $this->_model = new Mage_Core_Model_Layout_Argument_Handler_Url(
            array('objectFactory' => $this->_objectFactoryMock, 'urlModel' => $this->_urlModelMock)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Required url model is missing
     */
    public function testHandlerCreationIfUrlModelIsMissing()
    {
        new Mage_Core_Model_Layout_Argument_Handler_Url(
            array('objectFactory' => $this->_objectFactoryMock)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Wrong url model passed
     */
    public function testHandlerCreationIfUrlModelIsIncorrect()
    {
        new Mage_Core_Model_Layout_Argument_Handler_Url(
            array('objectFactory' => $this->_objectFactoryMock, 'urlModel' => new StdClass())
        );
    }

    public function testProcess()
    {
        $expectedUrl = "http://generated-url.com?___SID=U";

        $path = 'module/controller/action';
        $params = array('___SID' => "U");

        $this->_urlModelMock->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo($path), $this->equalTo($params))
            ->will($this->returnValue($expectedUrl));

        $this->assertEquals($expectedUrl, $this->_model->process(array('path' => $path, 'params' => $params)));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Passed value has incorrect format
     */
    public function testProcessIfValueIsNotArray()
    {
        $this->_model->process('*/*/action');
    }
}
