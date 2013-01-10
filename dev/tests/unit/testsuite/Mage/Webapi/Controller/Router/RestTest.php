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
 * @package     Mage_Webapi
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Webapi_Controller_Router_RestTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Controller_Router_Route_Rest */
    protected $_routeMock;

    /** @var Mage_Webapi_Controller_Request_Rest */
    protected $_request;

    /** @var Mage_Webapi_Helper_Data */
    protected $_helperMock;

    /** @var Mage_Webapi_Model_Config_Rest */
    protected $_apiConfigMock;

    /** @var Mage_Webapi_Controller_Router_Rest */
    protected $_router;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_apiConfigMock = $this->getMockBuilder('Mage_Webapi_Model_Config_Rest')
            ->disableOriginalConstructor()
            ->getMock();
        $interpreterFactory = $this->getMockBuilder('Mage_Webapi_Controller_Request_Rest_Interpreter_Factory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_helperMock = $this->getMockBuilder('Mage_Webapi_Helper_Data')
            ->disableOriginalConstructor()
            ->setMethods(array('__'))
            ->getMock();
        $this->_helperMock->expects($this->any())->method('__')->will($this->returnArgument(0));
        $this->_routeMock = $this->getMockBuilder('Mage_Webapi_Controller_Router_Route_Rest')
            ->disableOriginalConstructor()
            ->setMethods(array('match'))
            ->getMock();
        $this->_request = new Mage_Webapi_Controller_Request_Rest($interpreterFactory, $this->_helperMock);
        /** Initialize SUT. */
        $this->_router = new Mage_Webapi_Controller_Router_Rest($this->_helperMock, $this->_apiConfigMock);
    }

    protected function tearDown()
    {
        unset($this->_routeMock);
        unset($this->_request);
        unset($this->_helperMock);
        unset($this->_apiConfigMock);
        unset($this->_router);
        parent::tearDown();
    }

    public function testMatch()
    {
        $this->_apiConfigMock->expects($this->once())
            ->method('getAllRestRoutes')
            ->will($this->returnValue(array($this->_routeMock)));
        $this->_routeMock->expects($this->once())
            ->method('match')
            ->with($this->_request)
            ->will($this->returnValue(array()));

        $matchedRoute = $this->_router->match($this->_request);
        $this->assertEquals($this->_routeMock, $matchedRoute);
    }

    /**
     * @expectedException Mage_Webapi_Exception
     */
    public function testNotMatch()
    {
        $this->_apiConfigMock->expects($this->once())
            ->method('getAllRestRoutes')
            ->will($this->returnValue(array($this->_routeMock)));
        $this->_routeMock
            ->expects($this->once())
            ->method('match')
            ->with($this->_request)
            ->will($this->returnValue(false));

        $this->_router->match($this->_request);
    }

    public function testCheckRoute()
    {
        /** Prepare mocks for SUT constructor. */
        $checkRouteData = $this->_prepareMockDataForCheckRouteTest();
        $this->_routeMock->expects($this->once())
            ->method('match')
            ->with($checkRouteData['request'])
            ->will($this->returnValue(true));

        /** Execute SUT. */
        $this->_router->checkRoute(
            $checkRouteData['request'],
            $checkRouteData['methodName'],
            $checkRouteData['version']
        );
    }

    public function testCheckRouteException()
    {
        /** Prepare mocks for SUT constructor. */
        $checkRouteData = $this->_prepareMockDataForCheckRouteTest();
        $this->_routeMock->expects($this->once())
            ->method('match')
            ->with($checkRouteData['request'])
            ->will($this->returnValue(false));
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'Request does not match any route.',
            Mage_Webapi_Exception::HTTP_NOT_FOUND
        );
        /** Execute SUT. */
        $this->_router->checkRoute(
            $checkRouteData['request'],
            $checkRouteData['methodName'],
            $checkRouteData['version']
        );
    }

    /**
     * Prepare mocks for SUT constructor for testCheckRoute().
     *
     * @return array
     */
    protected function _prepareMockDataForCheckRouteTest()
    {
        $methodName = 'foo';
        $version = 'bar';
        $request = $this->getMockBuilder('Mage_Webapi_Controller_Request_Rest')
            ->disableOriginalConstructor()
            ->setMethods(array('getResourceName'))
            ->getMock();
        $resourceName = 'Resource Name';
        $request->expects($this->once())
            ->method('getResourceName')
            ->will($this->returnValue($resourceName));
        $this->_apiConfigMock->expects($this->once())
            ->method('getMethodRestRoutes')
            ->with($resourceName, $methodName, $version)
            ->will($this->returnValue(array($this->_routeMock)));
        return array('request' => $request, 'methodName' => $methodName, 'version' => $version);
    }
}
