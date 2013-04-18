<?php
/**
 * Test Webapi Front Controller.
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
class Mage_Webapi_Controller_FrontTest extends PHPUnit_Framework_TestCase
{
    const WEBAPI_AREA_FRONT_NAME = 'webapi';

    /** @var Mage_Webapi_Controller_Front */
    protected $_frontControllerMock;

    /** @var Magento_Controller_Router_Route_Factory. */
    protected $_routeFactoryMock;

    /** @var Mage_Webapi_Controller_Dispatcher_Factory. */
    protected $_dispatcherFactory;

    /** @var Mage_Webapi_Controller_Dispatcher_ErrorProcessor. */
    protected $_errorProcessorMock;

    /** @var Mage_Core_Model_Config */
    protected $_configMock;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $helper = $this->getMockBuilder('Mage_Webapi_Helper_Data')
            ->disableOriginalConstructor()
            ->setMethods(array('__'))
            ->getMock();
        $helper->expects($this->any())->method('__')->will($this->returnArgument(0));
        $helperFactory = $this->getMock('Mage_Core_Model_Factory_Helper');
        $helperFactory->expects($this->any())->method('get')->will($this->returnValue($helper));

        $this->_configMock = $this->getMockBuilder('Mage_Core_Model_Config')->disableOriginalConstructor()->getMock();
        $this->_configMock->expects($this->any())->method('getAreaFrontName')->will(
            $this->returnValue(self::WEBAPI_AREA_FRONT_NAME)
        );

        $this->_dispatcherFactory = $this->getMockBuilder('Mage_Webapi_Controller_Dispatcher_Factory')
            ->disableOriginalConstructor()->getMock();
        $application = $this->getMockBuilder('Mage_Core_Model_App')->disableOriginalConstructor()->getMock();
        $application->expects($this->any())->method('getConfig')->will($this->returnValue($this->_configMock));

        $this->_routeFactoryMock = $this->getMockBuilder('Magento_Controller_Router_Route_Factory')
            ->disableOriginalConstructor()->getMock();
        $this->_errorProcessorMock = $this->getMockBuilder('Mage_Webapi_Controller_Dispatcher_ErrorProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        /** Initialize SUT. */
        $this->_frontControllerMock = new Mage_Webapi_Controller_Front(
            $helperFactory,
            $this->_dispatcherFactory,
            $application,
            $this->_routeFactoryMock,
            $this->_errorProcessorMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_frontControllerMock);
        unset($this->_errorProcessorMock);
        unset($this->_dispatcherFactory);
        unset($this->_routeFactoryMock);
        parent::tearDown();
    }

    public function testGetListOfAvailableApiTypes()
    {
        $expectedApiTypes = array('rest', 'soap');
        $this->assertEquals(
            $expectedApiTypes,
            $this->_frontControllerMock->getListOfAvailableApiTypes(),
            'Not expected API types.'
        );
    }

    /**
     * Exception throwing logic for testInitWithException method.
     *
     * @throws Mage_Webapi_Exception
     */
    public function callbackThrowWebapiExcepion()
    {
        throw new Mage_Webapi_Exception('Message', Mage_Webapi_Exception::HTTP_BAD_REQUEST);
    }

    /**
     * Test dispatch method.
     */
    public function testDispatch()
    {
        $this->_createMockForApiRouteAndFactory(array('api_type' => Mage_Webapi_Controller_Front::API_TYPE_REST));
        $restDispatcherMock = $this->getMockBuilder('Mage_Webapi_Controller_Dispatcher_Rest')
            ->disableOriginalConstructor()
            ->getMock();
        /** Assert that handle method in mocked object will be executed only once. */
        $restDispatcherMock->expects($this->once())->method('dispatch');
        $this->_dispatcherFactory->expects($this->any())->method('get')
            ->will($this->returnValue($restDispatcherMock));
        $this->_frontControllerMock->dispatch();
    }

    /**
     * Test dispatch method with exception.
     */
    public function testDispatchException()
    {
        $this->_createMockForApiRouteAndFactory(array('api_type' => Mage_Webapi_Controller_Front::API_TYPE_REST));
        $restDispatcherMock = $this->getMockBuilder('Mage_Webapi_Controller_Dispatcher_Rest')
            ->disableOriginalConstructor()
            ->getMock();
        /** Init Logical exception. */
        $logicalException = new LogicException();
        /** Mock dispatcher to throw Logical exception. */
        $restDispatcherMock->expects($this->any())->method('dispatch')->will($this->throwException($logicalException));
        $this->_dispatcherFactory->expects($this->any())->method('get')->will($this->returnValue($restDispatcherMock));
        /** Assert that error processor renderException method will be executed with Logical Exception. */
        $this->_errorProcessorMock->expects($this->once())->method('renderException')->with(
            $this->equalTo($logicalException)
        );
        $this->_frontControllerMock->dispatch();
    }

    /**
     * Test DetermineApiType method with Not defined API Type.
     */
    public function testDetermineApiTypeNotDefined()
    {
        $apiType = array('api_type' => 'invalidApiType');
        $this->_createMockForApiRouteAndFactory($apiType);
        /** Assert Mage_Webapi_Exception type and message. */
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'The "%s" API type is not defined.',
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        $this->_frontControllerMock->determineApiType();
    }

    /**
     * Test DeteminateApiType method without API Type specification.
     */
    public function testDetermineApiTypeInvalidRoute()
    {
        $apiType = false;
        $this->_createMockForApiRouteAndFactory($apiType);
        /** Assert Mage_Webapi_Exception type and message. */
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Request does not match any API type route.',
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        $this->_frontControllerMock->determineApiType();
    }

    /**
     * Create mock for API Route and Route Factory objects.
     */
    protected function _createMockForApiRouteAndFactory($apiType)
    {
        $apiRouteMock = $this->getMockBuilder('Mage_Webapi_Controller_Router_Route')
            ->disableOriginalConstructor()->getMock();
        $apiRouteMock->expects($this->any())->method('match')->will($this->returnValue($apiType));
        $this->_routeFactoryMock->expects($this->any())->method('createRoute')->will(
            $this->returnValue($apiRouteMock)
        );
    }

    public function testDeterminateApiTypeApiIsSet()
    {
        $this->_createMockForApiRouteAndFactory(array('api_type' => Mage_Webapi_Controller_Front::API_TYPE_SOAP));
        /** Assert that createRoute method will be executed only once */
        $this->_routeFactoryMock->expects($this->once())->method('createRoute');
        /** The first method call will set apiType property using createRoute method. */
        $this->_frontControllerMock->determineApiType();
        /** The second method call should use set apiType and should not trigger createRoute method. */
        $this->_frontControllerMock->determineApiType();
    }
}
