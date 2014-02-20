<?php
/**
 * Test Rest controller.
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Controller;

class RestTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Controller\Rest */
    protected $_restController;

    /** @var \Magento\Webapi\Controller\Rest\Request */
    protected $_requestMock;

    /** @var \Magento\Webapi\Controller\Rest\Response */
    protected $_responseMock;

    /** @var \Magento\Webapi\Controller\Rest\Router */
    protected $_routerMock;

    /** @var \Magento\Webapi\Controller\Rest\Router\Route */
    protected $_routeMock;

    /** @var \Magento\ObjectManager */
    protected $_objectManagerMock;

    /** @var \stdClass */
    protected $_serviceMock;

    /** @var \Magento\App\State */
    protected $_appStateMock;

    /** @var \Magento\Oauth\Oauth */
    protected $_oauthServiceMock;

    /** @var \Magento\Oauth\Helper\Request */
    protected $_oauthHelperMock;

    /** @var \Magento\Authz\Service\AuthorizationV1Interface */
    protected $_authzServiceMock;

    const SERVICE_METHOD = 'testMethod';
    const SERVICE_ID = 'Magento\Webapi\Controller\TestService';

    protected function setUp()
    {
        $this->_requestMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\Request')
            ->setMethods(array('isSecure', 'getRequestData'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->_responseMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\Response')
            ->setMethods(array('sendResponse', 'getHeaders', 'prepareResponse'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->_routerMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\Router')
            ->setMethods(array('match'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->_routeMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\Router\Route')
            ->setMethods(array('isSecure', 'getServiceMethod', 'getServiceClass'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->_objectManagerMock = $this->getMockBuilder('Magento\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_serviceMock = $this->getMockBuilder(self::SERVICE_ID)
            ->setMethods(array(self::SERVICE_METHOD))
            ->disableOriginalConstructor()
            ->getMock();

        $this->_appStateMock =  $this->getMockBuilder('Magento\App\State')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_oauthServiceMock = $this->getMockBuilder('Magento\Oauth\Oauth')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_oauthHelperMock = $this->getMockBuilder('Magento\Oauth\Helper\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_authzServiceMock = $this->getMockBuilder('Magento\Authz\Service\AuthorizationV1Interface')
            ->disableOriginalConstructor()
            ->getMock();

        $errorProcessorMock = $this->getMock('Magento\Webapi\Controller\ErrorProcessor', [], [], '', false);
        $errorProcessorMock->expects($this->any())->method('maskException')->will($this->returnArgument(0));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $serializer = $objectManager->getObject('Magento\Webapi\Controller\ServiceArgsSerializer');

        /** Init SUT. */
        $this->_restController = new \Magento\Webapi\Controller\Rest(
            $this->_requestMock,
            $this->_responseMock,
            $this->_routerMock,
            $this->_objectManagerMock,
            $this->_appStateMock,
            $this->_oauthServiceMock,
            $this->_oauthHelperMock,
            $this->_authzServiceMock,
            $serializer,
            $errorProcessorMock
        );

        // Set default expectations used by all tests
        $this->_routeMock
            ->expects($this->any())->method('getServiceClass')->will($this->returnValue(self::SERVICE_ID));

        $this->_routeMock
            ->expects($this->any())->method('getServiceMethod')->will($this->returnValue(self::SERVICE_METHOD));
        $this->_routerMock->expects($this->any())->method('match')->will($this->returnValue($this->_routeMock));

        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValue($this->_serviceMock));
        $this->_responseMock->expects($this->any())->method('prepareResponse')->will($this->returnValue(array()));
        $this->_requestMock->expects($this->any())->method('getRequestData')->will($this->returnValue(array()));
        $this->_serviceMock->expects($this->any())->method(self::SERVICE_METHOD)->will($this->returnValue(null));

        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_restController);
        unset($this->_requestMock);
        unset($this->_responseMock);
        unset($this->_routerMock);
        unset($this->_objectManagerMock);
        unset($this->_oauthServiceMock);
        unset($this->_oauthHelperMock);
        unset($this->_appStateMock);
        parent::tearDown();
    }

    /**
     * Test redirected to install page
     */
    public function testRedirectToInstallPage()
    {
        $this->_appStateMock->expects($this->any())->method('isInstalled')->will($this->returnValue(false));
        $expectedMsg = 'Magento is not yet installed';

        $this->_restController->dispatch($this->_requestMock);
        $this->assertTrue($this->_responseMock->isException());
        $exceptionArray = $this->_responseMock->getException();
        $this->assertEquals($expectedMsg, $exceptionArray[0]->getMessage());
    }

    /**
     * Test Secure Request and Secure route combinations
     *
     * @dataProvider dataProviderSecureRequestSecureRoute
     */
    public function testSecureRouteAndRequest($isSecureRoute, $isSecureRequest)
    {
        $this->_appStateMock->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        $this->_serviceMock
            ->expects($this->any())->method(self::SERVICE_METHOD)->will($this->returnValue(array()));
        $this->_routeMock->expects($this->any())->method('isSecure')->will($this->returnValue($isSecureRoute));
        $this->_requestMock->expects($this->any())->method('isSecure')->will($this->returnValue($isSecureRequest));
        $this->_authzServiceMock->expects($this->once())->method('isAllowed')->will($this->returnValue(true));
        $this->_restController->dispatch($this->_requestMock);
        $this->assertFalse($this->_responseMock->isException());
    }

    /**
     * Data provider for testSecureRouteAndRequest.
     *
     * @return array
     */
    public function dataProviderSecureRequestSecureRoute()
    {
        return array(
            //Each array contains return type for isSecure method of route and request objects .
            array(
                true,
                true
            ),
            array(
                false,
                true
            ),
            array(
                false,
                false
            )
        );

    }

    /**
     * Test insecure request for a secure route
     */
    public function testInSecureRequestOverSecureRoute()
    {
        $this->_appStateMock->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        $this->_serviceMock->expects($this->any())->method(self::SERVICE_METHOD)->will($this->returnValue(array()));
        $this->_routeMock->expects($this->any())->method('isSecure')->will($this->returnValue(true));
        $this->_requestMock->expects($this->any())->method('isSecure')->will($this->returnValue(false));
        $this->_authzServiceMock->expects($this->once())->method('isAllowed')->will($this->returnValue(true));

        // Override default prepareResponse. It should never be called in this case
        $this->_responseMock->expects($this->never())->method('prepareResponse');

        $this->_restController->dispatch($this->_requestMock);
        $this->assertTrue($this->_responseMock->isException());
        $exceptionArray = $this->_responseMock->getException();
        $this->assertEquals('Operation allowed only in HTTPS', $exceptionArray[0]->getMessage());
        $this->assertEquals(\Magento\Webapi\Exception::HTTP_BAD_REQUEST, $exceptionArray[0]->getHttpCode());
    }

    public function testAuthorizationFailed()
    {
        $this->_appStateMock->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        $this->_authzServiceMock->expects($this->once())->method('isAllowed')->will($this->returnValue(false));

        $this->_restController->dispatch($this->_requestMock);
        /** Ensure that response contains proper error message. */
        $expectedMsg = 'Not Authorized.';
        $this->assertTrue($this->_responseMock->isException());
        $exceptionArray = $this->_responseMock->getException();
        $this->assertEquals($expectedMsg, $exceptionArray[0]->getMessage());
    }
}

class TestService
{
    public function testMethod()
    {
        return null;
    }
}
