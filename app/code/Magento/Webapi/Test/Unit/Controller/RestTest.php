<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Webapi\Test\Unit\Controller;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthorizationException;

/**
 * Test Rest controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Controller\Rest
     */
    protected $_restController;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response
     */
    protected $_responseMock;

    /**
     * @var \Magento\Webapi\Controller\Rest\Router
     */
    protected $_routerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Webapi\Controller\Rest\Router\Route
     */
    protected $_routeMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManagerMock;

    /**
     * @var \stdClass
     */
    protected $_serviceMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    /**
     * @var \Magento\Framework\Oauth\OauthInterface
     */
    protected $_oauthServiceMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorizationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $areaListMock;

    /**
     * @var \Magento\Framework\Webapi\ServiceInputProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceInputProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $areaMock;
    
    /**
     * @var \Magento\Webapi\Controller\Rest\ParamsOverrider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paramsOverriderMock;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectProcessorMock;

    const SERVICE_METHOD = 'testMethod';

    const SERVICE_ID = 'Magento\Webapi\Controller\TestService';

    protected function mockArguments()
    {
        $this->_requestMock = $this->getMockBuilder('Magento\Framework\Webapi\Rest\Request')
            ->setMethods(['isSecure', 'getRequestData'])->disableOriginalConstructor()->getMock();
        $this->_responseMock = $this->getMockBuilder('Magento\Framework\Webapi\Rest\Response')
            ->setMethods(['sendResponse', 'getHeaders', 'prepareResponse'])->disableOriginalConstructor()->getMock();
        $this->_routerMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\Router')->setMethods(['match'])
            ->disableOriginalConstructor()->getMock();
        $this->_routeMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\Router\Route')
            ->setMethods(['isSecure', 'getServiceMethod', 'getServiceClass', 'getAclResources', 'getParameters'])
            ->disableOriginalConstructor()->getMock();
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_serviceMock = $this->getMockBuilder(self::SERVICE_ID)->setMethods([self::SERVICE_METHOD])
            ->disableOriginalConstructor()->getMock();
        $this->_appStateMock = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()->getMock();
        $this->_oauthServiceMock = $this->getMockBuilder('\Magento\Framework\Oauth\OauthInterface')
            ->setMethods(['validateAccessTokenRequest'])->getMockForAbstractClass();
        $this->_authorizationMock = $this->getMockBuilder('Magento\Framework\AuthorizationInterface')
            ->disableOriginalConstructor()->getMock();
        $this->paramsOverriderMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\ParamsOverrider')
            ->setMethods(['overrideParams'])
            ->disableOriginalConstructor()->getMock();
        $this->dataObjectProcessorMock = $this->getMockBuilder('Magento\Framework\Reflection\DataObjectProcessor')
            ->disableOriginalConstructor()->setMethods(['getMethodReturnType'])->getMockForAbstractClass();
    }

    protected function setUp()
    {
        $this->mockArguments();

        $layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()->getMock();
        $errorProcessorMock = $this->getMock('Magento\Framework\Webapi\ErrorProcessor', [], [], '', false);
        $errorProcessorMock->expects($this->any())->method('maskException')->will($this->returnArgument(0));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->serviceInputProcessorMock = $this->getMockBuilder('\Magento\Framework\Webapi\ServiceInputProcessor')
            ->disableOriginalConstructor()->setMethods(['process'])->getMock();
        $this->areaListMock = $this->getMock('\Magento\Framework\App\AreaList', [], [], '', false);
        $this->areaMock = $this->getMock('Magento\Framework\App\AreaInterface');
        $this->areaListMock->expects($this->any())->method('getArea')->will($this->returnValue($this->areaMock));
        /** Init SUT. */
        $this->_restController =
            $objectManager->getObject('Magento\Webapi\Controller\Rest',
                [
                    'request' => $this->_requestMock,
                    'response' => $this->_responseMock,
                    'router' => $this->_routerMock,
                    'objectManager' => $this->_objectManagerMock,
                    'appState' => $this->_appStateMock,
                    'layout' => $layoutMock,
                    'oauthService' => $this->_oauthServiceMock,
                    'authorization' => $this->_authorizationMock,
                    'serviceInputProcessor' => $this->serviceInputProcessorMock,
                    'errorProcessor' => $errorProcessorMock,
                    'areaList' => $this->areaListMock,
                    'paramsOverrider' => $this->paramsOverriderMock,
                    'dataObjectProcessor' => $this->dataObjectProcessorMock
                ]
            );
        // Set default expectations used by all tests
        $this->_routeMock->expects($this->any())->method('getServiceClass')->will($this->returnValue(self::SERVICE_ID));
        $this->_routeMock->expects($this->any())->method('getServiceMethod')
            ->will($this->returnValue(self::SERVICE_METHOD));
        $this->_routerMock->expects($this->any())->method('match')->will($this->returnValue($this->_routeMock));
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValue($this->_serviceMock));
        $this->_responseMock->expects($this->any())->method('prepareResponse')->will($this->returnValue([]));
        $this->_serviceMock->expects($this->any())->method(self::SERVICE_METHOD)->will($this->returnValue(null));
        $this->dataObjectProcessorMock->expects($this->any())->method('getMethodReturnType')
            ->with(self::SERVICE_ID, self::SERVICE_METHOD)
            ->will($this->returnValue('null'));
        $this->paramsOverriderMock->expects($this->any())->method('overrideParams')->will($this->returnValue([]));
        parent::setUp();
    }

    /**
     * Test Secure Request and Secure route combinations
     *
     * @dataProvider dataProviderSecureRequestSecureRoute
     */
    public function testSecureRouteAndRequest($isSecureRoute, $isSecureRequest)
    {
        $this->_serviceMock->expects($this->any())->method(self::SERVICE_METHOD)->will($this->returnValue([]));
        $this->_routeMock->expects($this->any())->method('isSecure')->will($this->returnValue($isSecureRoute));
        $this->_routeMock->expects($this->once())->method('getParameters')->will($this->returnValue([]));
        $this->_routeMock->expects($this->any())->method('getAclResources')->will($this->returnValue(['1']));
        $this->_requestMock->expects($this->any())->method('getRequestData')->will($this->returnValue([]));
        $this->_requestMock->expects($this->any())->method('isSecure')->will($this->returnValue($isSecureRequest));
        $this->_authorizationMock->expects($this->once())->method('isAllowed')->will($this->returnValue(true));
        $this->serviceInputProcessorMock->expects($this->any())->method('process')->will($this->returnValue([]));
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
        // Each array contains return type for isSecure method of route and request objects.
        return [[true, true], [false, true], [false, false]];
    }

    /**
     * Test insecure request for a secure route
     */
    public function testInSecureRequestOverSecureRoute()
    {
        $this->_serviceMock->expects($this->any())->method(self::SERVICE_METHOD)->will($this->returnValue([]));
        $this->_routeMock->expects($this->any())->method('isSecure')->will($this->returnValue(true));
        $this->_routeMock->expects($this->any())->method('getAclResources')->will($this->returnValue(['1']));
        $this->_requestMock->expects($this->any())->method('isSecure')->will($this->returnValue(false));
        $this->_authorizationMock->expects($this->once())->method('isAllowed')->will($this->returnValue(true));

        // Override default prepareResponse. It should never be called in this case
        $this->_responseMock->expects($this->never())->method('prepareResponse');

        $this->_restController->dispatch($this->_requestMock);
        $this->assertTrue($this->_responseMock->isException());
        $exceptionArray = $this->_responseMock->getException();
        $this->assertEquals('Operation allowed only in HTTPS', $exceptionArray[0]->getMessage());
        $this->assertEquals(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST, $exceptionArray[0]->getHttpCode());
    }

    public function testAuthorizationFailed()
    {
        $this->_authorizationMock->expects($this->once())->method('isAllowed')->will($this->returnValue(false));
        $this->_oauthServiceMock->expects(
            $this->any())->method('validateAccessTokenRequest')->will($this->returnValue('fred')
            );
        $this->_routeMock->expects($this->any())->method('getAclResources')->will($this->returnValue(['5', '6']));

        $this->_restController->dispatch($this->_requestMock);
        /** Ensure that response contains proper error message. */
        $expectedMsg = 'Consumer is not authorized to access 5, 6';
        AuthorizationException::NOT_AUTHORIZED;
        $this->assertTrue($this->_responseMock->isException());
        $exceptionArray = $this->_responseMock->getException();
        $this->assertEquals($expectedMsg, $exceptionArray[0]->getMessage());
    }
}

class TestService
{
    /**
     * @return null
     */
    public function testMethod()
    {
        return null;
    }
}
