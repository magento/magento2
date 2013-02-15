<?php
/**
 * Test Webapi Json Interpreter Request Rest Controller.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Controller_Request_Rest_Interpreter_JsonTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_helperFactoryMock;

    /** @var Mage_Webapi_Controller_Request_Rest_Interpreter_Json */
    protected $_jsonInterpreter;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_coreHelperMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_appMock;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_helperFactoryMock = $this->getMock('Mage_Core_Model_Factory_Helper');
        $this->_coreHelperMock = $this->getMock('Mage_Core_Helper_Data',
            array('__', 'jsonDecode'), array(), '', false, false
        );
        $this->_helperFactoryMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->_coreHelperMock));
        $this->_appMock = $this->getMockBuilder('Mage_Core_Model_App')
            ->setMethods(array('isDeveloperMode'))
            ->disableOriginalConstructor()
            ->getMock();
        /** Initialize SUT. */
        $this->_jsonInterpreter = new Mage_Webapi_Controller_Request_Rest_Interpreter_Json(
            $this->_helperFactoryMock,
            $this->_appMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_helperFactoryMock);
        unset($this->_jsonInterpreter);
        unset($this->_coreHelperMock);
        unset($this->_appMock);
        parent::tearDown();
    }

    public function testInterpretInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException', '"boolean" data type is invalid. String is expected.');
        $this->_jsonInterpreter->interpret(false);
    }

    public function testInterpret()
    {
        /** Prepare mocks for SUT constructor. */
        $inputEncodedJson = '{"key1":"test1","key2":"test2","array":{"test01":"some1","test02":"some2"}}';
        $expectedDecodedJson = array(
            'key1' => 'test1',
            'key2' => 'test2',
            'array' => array(
                'test01' => 'some1',
                'test02' => 'some2',
            )
        );
        $this->_coreHelperMock->expects($this->once())
            ->method('jsonDecode')
            ->will($this->returnValue($expectedDecodedJson));
        /** Initialize SUT. */
        $this->assertEquals(
            $expectedDecodedJson,
            $this->_jsonInterpreter->interpret($inputEncodedJson),
            'Interpretation from JSON to array is invalid.'
        );
    }

    public function testInterpretInvalidEncodedBodyExceptionDeveloperModeOn()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_coreHelperMock->expects($this->once())
            ->method('jsonDecode')
            ->will($this->throwException(new Zend_Json_Exception));
        $this->_appMock->expects($this->once())
            ->method('isDeveloperMode')
            ->will($this->returnValue(false));
        $this->_coreHelperMock->expects($this->any())->method('__')->will($this->returnArgument(0));
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Decoding error.',
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        /** Initialize SUT. */
        $inputInvalidJson = '{"key1":"test1"."key2":"test2"}';
        $this->_jsonInterpreter->interpret($inputInvalidJson);
    }

    public function testInterpretInvalidEncodedBodyExceptionDeveloperModeOff()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_coreHelperMock->expects($this->once())
            ->method('jsonDecode')
            ->will(
            $this->throwException(
                new Zend_Json_Exception('Decoding error:' . PHP_EOL . 'Decoding failed: Syntax error')
            )
        );
        $this->_appMock->expects($this->once())
            ->method('isDeveloperMode')
            ->will($this->returnValue(true));
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Decoding error:' . PHP_EOL . 'Decoding failed: Syntax error',
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        /** Initialize SUT. */
        $inputInvalidJson = '{"key1":"test1"."key2":"test2"}';
        $this->_jsonInterpreter->interpret($inputInvalidJson);
    }
}



