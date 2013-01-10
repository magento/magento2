<?php
/**
 *
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
class Mage_Webapi_Controller_Request_Rest_Interpreter_XmlTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_helperFactoryMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_apiHelperMock;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_xmlParserMock;

    /** @var Mage_Webapi_Controller_Request_Rest_Interpreter_Xml */
    protected $_xmlInterpreter;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_appMock;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_helperFactoryMock = $this->getMock('Mage_Core_Model_Factory_Helper');
        $this->_apiHelperMock = $this->getMockBuilder('Mage_Webapi_Helper_Data')
            ->disableOriginalConstructor()
            ->setMethods(array('__'))
            ->getMock();
        $this->_apiHelperMock->expects($this->any())->method('__')->will($this->returnArgument(0));
        $this->_helperFactoryMock->expects($this->any())->method('get')
            ->will($this->returnValue($this->_apiHelperMock));
        $this->_xmlParserMock = $this->getMock('Mage_Xml_Parser', array('xmlToArray', 'loadXML'));
        $this->_appMock = $this->getMockBuilder('Mage_Core_Model_App')
            ->setMethods(array('isDeveloperMode'))
            ->disableOriginalConstructor()
            ->getMock();
        /** Initialize SUT. */
        $this->_xmlInterpreter = new Mage_Webapi_Controller_Request_Rest_Interpreter_Xml(
            $this->_xmlParserMock,
            $this->_helperFactoryMock,
            $this->_appMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_helperFactoryMock);
        unset($this->_apiHelperMock);
        unset($this->_xmlInterpreter);
        unset($this->_xmlParserMock);
        unset($this->_appMock);
        parent::tearDown();
    }

    public function testInterpretInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid data type "boolean". String is expected.');
        $this->_xmlInterpreter->interpret(false);
    }

    public function testInterpret()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_xmlParserMock->expects($this->once())->method('loadXML');
        $validInputXml = '<?xml version="1.0"?><xml><key1>test1</key1><key2>test2</key2></xml>';
        $returnArray = array('xml' => array('key1' => 'test1', 'key2' => 'test2'));
        $this->_xmlParserMock->expects($this->once())->method('xmlToArray')->will($this->returnValue($returnArray));
        $expectedArray = array('key1' => 'test1', 'key2' => 'test2');
        /** Initialize SUT. */
        $this->assertEquals(
            $expectedArray,
            $this->_xmlInterpreter->interpret($validInputXml),
            'Request xml body was parsed incorrectly into array of params'
        );
    }

    public function testHandleErrors()
    {
        /** Add message to error dispatcher. */
        $firstErrorMessage = "No document type declaration. ";
        $this->_xmlInterpreter->handleErrors(null, $firstErrorMessage, null, null);
        /** Assert that first error message was added to error dispatcher. */
        $this->assertAttributeEquals(
            $firstErrorMessage,
            '_errorMessage',
            $this->_xmlInterpreter,
            'Error message was not set to error dispatcher.'
        );
        /** Add new message to error dispatcher. */
        $secondErrorMessage = "Strings should be wrapped in double quotes.";
        $expectedMessages = $firstErrorMessage . $secondErrorMessage;
        $this->_xmlInterpreter->handleErrors(null, $secondErrorMessage, null, null);
        /** Assert that both error messages were added to error dispatcher. */
        $this->assertAttributeEquals(
            $expectedMessages,
            '_errorMessage',
            $this->_xmlInterpreter,
            'Error messages were not set to error dispatcher.'
        );
    }

    public function testInterpretMageWebapiExceptionDeveloperModeOn()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_appMock->expects($this->once())
            ->method('isDeveloperMode')
            ->will($this->returnValue(true));
        $errorMessage = 'End tag for "key1" was omitted.';
        $this->_xmlInterpreter->handleErrors(null, $errorMessage, null, null);
        $this->_xmlParserMock->expects($this->once())->method('loadXML');
        $invalidXml = '<?xml version="1.0"?><xml><key1>test1</xml>';
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Decoding Error: End tag for "key1" was omitted.',
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        /** Initialize SUT. */
        $this->_xmlInterpreter->interpret($invalidXml);
    }

    public function testInterpretMageWebapiExceptionDeveloperModeOff()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_appMock->expects($this->once())
            ->method('isDeveloperMode')
            ->will($this->returnValue(false));
        $errorMessage = 'End tag for "key1" was omitted.';
        $this->_xmlInterpreter->handleErrors(null, $errorMessage, null, null);
        $this->_xmlParserMock->expects($this->once())->method('loadXML');
        $invalidXml = '<?xml version="1.0"?><xml><key1>test1</xml>';
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Decoding error.',
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        /** Initialize SUT. */
        $this->_xmlInterpreter->interpret($invalidXml);
    }
}
