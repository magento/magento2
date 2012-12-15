<?php
/**
 * Test Webapi Error Processor.
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Controller_Dispatcher_ErrorProcessorTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Controller_Dispatcher_ErrorProcessor */
    protected $_errorProcessor;

    /** @var Mage_Webapi_Helper_Data */
    protected $_helperMock;

    /** @var Mage_Core_Model_App */
    protected $_appMock;

    /** @var Mage_Core_Model_Logger */
    protected $_loggerMock;

    protected function setUp()
    {
        /** Set up mocks for SUT. */
        $this->_helperMock = $this->getMockBuilder('Mage_Core_Helper_Data')->getMock();
        $this->_helperMock->expects($this->any())->method('__')->will($this->returnArgument(0));
        $helperFactoryMock = $this->getMockBuilder('Mage_Core_Model_Factory_Helper')->getMock();
        $helperFactoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_helperMock));
        $this->_appMock = $this->getMockBuilder('Mage_Core_Model_App')->disableOriginalConstructor()->getMock();
        $this->_loggerMock = $this->getMockBuilder('Mage_Core_Model_Logger')->disableOriginalConstructor()->getMock();
        /** Initialize SUT. */
        $this->_errorProcessor = new Mage_Webapi_Controller_Dispatcher_ErrorProcessor(
            $helperFactoryMock,
            $this->_appMock,
            $this->_loggerMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_errorProcessor);
        unset($this->_helperMock);
        unset($this->_appMock);
        parent::tearDown();
    }

    /**
     * Test render method in Json format.
     */
    public function testRenderJson()
    {
        $_SERVER['HTTP_ACCEPT'] = 'json';
        /** Assert jsonEncode method will be executed once. */
        $this->_helperMock->expects($this->once())->method('jsonEncode')->will(
            $this->returnCallback(array($this, 'callbackJsonEncode'), $this->returnArgument(0))
        );
        /** Init output buffering to catch output via echo function. */
        ob_start();
        $this->_errorProcessor->render('Message');
        /** Get output buffer. */
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '{"messages":{"error":[{"code":500,"message":"Message"}]}}';
        $this->assertEquals($expectedResult, $actualResult, 'Wrong rendering in Json.');
    }

    /**
     * Callback function for RenderJson and RenderJsonInDeveloperMode tests.
     *
     * Method encode data to Json and return it.
     *
     * @param $data
     * @return string
     */
    public function callbackJsonEncode($data)
    {
        return json_encode($data);
    }

    /**
     * Test render method in Json format with turned on developer mode.
     */
    public function testRenderJsonInDeveloperMode()
    {
        $_SERVER['HTTP_ACCEPT'] = 'json';
        /** Mock app to return enabled developer mode flag. */
        $this->_appMock->expects($this->any())->method('isDeveloperMode')->will($this->returnValue(true));
        /** Assert jsonEncode method will be executed once. */
        $this->_helperMock->expects($this->once())->method('jsonEncode')->will(
            $this->returnCallback(array($this, 'callbackJsonEncode'), $this->returnArgument(0))
        );
        ob_start();
        $this->_errorProcessor->render('Message', 'Message trace.', 401);
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '{"messages":{"error":[{"code":401,"message":"Message","trace":"Message trace."}]}}';
        $this->assertEquals($expectedResult, $actualResult, 'Wrong rendering in Json.');
    }

    /**
     * Test render method in XML format.
     */
    public function testRenderXml()
    {
        $_SERVER['HTTP_ACCEPT'] = 'xml';
        /** Init output buffering to catch output via echo function. */
        ob_start();
        $this->_errorProcessor->render('Message');
        /** Get output buffer. */
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '<?xml version="1.0"?><error><messages><error><data_item><code>500</code>'
            . '<message>Message</message></data_item></error></messages></error>';
        $this->assertEquals($expectedResult, $actualResult, 'Wrong rendering in XML.');
    }

    /**
     * Test render method in XML format with turned on developer mode.
     */
    public function testRenderXmlInDeveloperMode()
    {
        $_SERVER['HTTP_ACCEPT'] = 'xml';
        /** Mock app to return enabled developer mode flag. */
        $this->_appMock->expects($this->any())->method('isDeveloperMode')->will($this->returnValue(true));
        /** Init output buffering to catch output via echo function. */
        ob_start();
        $this->_errorProcessor->render('Message', 'Trace message.', 401);
        /** Get output buffer. */
        $actualResult = ob_get_contents();
        ob_end_clean();
        $expectedResult = '<?xml version="1.0"?><error><messages><error><data_item><code>401</code><message>'
            . 'Message</message><trace><![CDATA[Trace message.]]></trace></data_item></error></messages></error>';
        $this->assertEquals($expectedResult, $actualResult, 'Wrong rendering in XML with turned on developer mode.');
    }

    /**
     * Test default render format is Json.
     */
    public function testRenderDefaultFormat()
    {
        /** Set undefined rendering format. */
        $_SERVER['HTTP_ACCEPT'] = 'undefined';
        /** Assert jsonEncode method will be executed at least once. */
        $this->_helperMock->expects($this->atLeastOnce())->method('jsonEncode');
        $this->_errorProcessor->render('Message');
    }

    /**
     * Test renderException method with Mage_Webapi_Exception.
     */
    public function testRenderExecutionWebapiException()
    {
        $this->markTestIncomplete("Think how to replace this test.");
        $_SERVER['HTTP_ACCEPT'] = 'json';
        /** Init Mage_Webapi_Exception. */
        $apiException = new Mage_Webapi_Exception('Exception message', 500);
        /** Assert jsonEncode will be executed once. */
        $this->_helperMock->expects($this->once())->method('jsonEncode');
        $this->_errorProcessor->renderException($apiException);
    }

    /**
     * Test renderException method with turned on Developer mode.
     */
    public function testRenderExecutionInDeveloperMode()
    {
        $this->markTestIncomplete("Think how to replace this test.");
        $_SERVER['HTTP_ACCEPT'] = 'json';
        /** Init base Exception object. */
        $exception = new Exception('Message');
        /** Mock app to return enabled developer mode flag. */
        $this->_appMock->expects($this->any())->method('isDeveloperMode')->will($this->returnValue(true));
        /** Assert jsonEncode will be executed once. */
        $this->_helperMock->expects($this->once())->method('jsonEncode');
        $this->_errorProcessor->renderException($exception);
    }

    /**
     * Test maskException method with Mage_Webapi_Exception.
     */
    public function testMaskWebapiException()
    {
        /** Init mage_webapi_Exception. */
        $apiException = new Mage_Webapi_Exception('Message', 400);
        /** Asser Webapi exception was not masked. */
        $this->assertEquals(
            $this->_errorProcessor->maskException($apiException),
            $apiException,
            'Webapi Exception was masked wrong.'
        );
    }

    /**
     * Test maskException method with turned on developer mode.
     */
    public function testMaskExceptionInDeveloperMode()
    {
        /** Mock app isDeveloperMode to return true. */
        $this->_appMock->expects($this->once())->method('isDeveloperMode')->will($this->returnValue(true));
        /** Init Logical exception. */
        $logicalException = new LogicException();
        /** Asser Webapi exception was not masked. */
        $this->assertEquals(
            $this->_errorProcessor->maskException($logicalException),
            $logicalException,
            'Exception was masked wrong in developer mode.'
        );
    }

    /**
     * Test maskException method with turned on developer mode.
     */
    public function testMaskNonWebapiException()
    {
        /** Assert exception was logged. */
        $this->_loggerMock->expects($this->once())->method('logException');
        $maskedException = $this->_errorProcessor->maskException(new LogicException());
        /** Assert masked exception type is Mage_Webapi_Exception. */
        $this->assertInstanceOf('Mage_Webapi_Exception', $maskedException, 'Masked exception type is not Webapi.');
        /** Asser masked exception code is 500. */
        $this->assertEquals(
            Mage_Webapi_Exception::HTTP_INTERNAL_ERROR,
            $maskedException->getCode(),
            'Masked exception code is wrong.'
        );
        /** Assert masked exception message. */
        $this->assertEquals(
            'Internal Error. Details are available in Magento log file. Report ID: "%s"',
            $maskedException->getMessage(),
            'Masked exception message is wrong.'
        );
    }
}
