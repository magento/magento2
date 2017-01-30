<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Controller\Rest
     */
    protected $_restController;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Webapi\Controller\Rest\Router\Route
     */
    protected $_routeMock;

    /**
     * @var \stdClass|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_serviceMock;

    /**
     * @var \Magento\Framework\Oauth\OauthInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_oauthServiceMock;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authorizationMock;

    /**
     * @var \Magento\Framework\Webapi\ServiceInputProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceInputProcessorMock;

    /**
     * @var \Magento\Webapi\Model\Rest\Swagger\Generator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $swaggerGeneratorMock;

    /** @var  \Magento\Store\Model\StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $storeManagerMock;

    /** @var  \Magento\Store\Api\Data\StoreInterface | \PHPUnit_Framework_MockObject_MockObject*/
    private $storeMock;

    const SERVICE_METHOD = 'testMethod';

    const SERVICE_ID = 'Magento\Webapi\Controller\TestService';

    protected function setUp()
    {
        $this->_requestMock = $this->getMockBuilder('Magento\Framework\Webapi\Rest\Request')
            ->setMethods(
                [
                    'isSecure', 'getRequestData', 'getParams', 'getParam', 'getRequestedServices', 'getPathInfo',
                    'getHttpHost', 'getMethod',
                ]
            )->disableOriginalConstructor()->getMock();
        $this->_requestMock->expects($this->any())
            ->method('getHttpHost')
            ->willReturn('testHostName.com');
        $this->_responseMock = $this->getMockBuilder('Magento\Framework\Webapi\Rest\Response')
            ->setMethods(['sendResponse', 'prepareResponse'])->disableOriginalConstructor()->getMock();
        $routerMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\Router')->setMethods(['match'])
            ->disableOriginalConstructor()->getMock();
        $this->_routeMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\Router\Route')
            ->setMethods(['isSecure', 'getServiceMethod', 'getServiceClass', 'getAclResources', 'getParameters'])
            ->disableOriginalConstructor()->getMock();
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_serviceMock = $this->getMockBuilder(self::SERVICE_ID)->setMethods([self::SERVICE_METHOD])
            ->disableOriginalConstructor()->getMock();
        $this->_oauthServiceMock = $this->getMockBuilder('\Magento\Framework\Oauth\OauthInterface')
            ->setMethods(['validateAccessTokenRequest'])->getMockForAbstractClass();
        $this->_authorizationMock = $this->getMockBuilder('Magento\Framework\AuthorizationInterface')
            ->disableOriginalConstructor()->getMock();
        $paramsOverriderMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\ParamsOverrider')
            ->setMethods(['overrideParams'])
            ->disableOriginalConstructor()->getMock();
        $dataObjectProcessorMock = $this->getMockBuilder('Magento\Framework\Reflection\DataObjectProcessor')
            ->disableOriginalConstructor()->setMethods(['getMethodReturnType'])->getMockForAbstractClass();
        $this->swaggerGeneratorMock = $this->getMockBuilder('Magento\Webapi\Model\Rest\Swagger\Generator')
            ->disableOriginalConstructor()
            ->setMethods(['generate', 'getListOfServices'])
            ->getMockForAbstractClass();

        $layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()->getMock();
        $errorProcessorMock = $this->getMock('Magento\Framework\Webapi\ErrorProcessor', [], [], '', false);
        $errorProcessorMock->expects($this->any())->method('maskException')->will($this->returnArgument(0));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->serviceInputProcessorMock = $this->getMockBuilder('\Magento\Framework\Webapi\ServiceInputProcessor')
            ->disableOriginalConstructor()->setMethods(['process'])->getMock();
        $areaListMock = $this->getMock('\Magento\Framework\App\AreaList', [], [], '', false);
        $areaMock = $this->getMock('Magento\Framework\App\AreaInterface');
        $areaListMock->expects($this->any())->method('getArea')->will($this->returnValue($areaMock));
        $this->storeMock = $this->getMock('\Magento\Store\Api\Data\StoreInterface');
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);

        /** Init SUT. */
        $this->_restController =
            $objectManager->getObject('Magento\Webapi\Controller\Rest',
                [
                    'request' => $this->_requestMock,
                    'response' => $this->_responseMock,
                    'router' => $routerMock,
                    'objectManager' => $objectManagerMock,
                    'layout' => $layoutMock,
                    'oauthService' => $this->_oauthServiceMock,
                    'authorization' => $this->_authorizationMock,
                    'serviceInputProcessor' => $this->serviceInputProcessorMock,
                    'errorProcessor' => $errorProcessorMock,
                    'areaList' => $areaListMock,
                    'paramsOverrider' => $paramsOverriderMock,
                    'dataObjectProcessor' => $dataObjectProcessorMock,
                    'swaggerGenerator' => $this->swaggerGeneratorMock,
                    'storeManager' => $this->storeManagerMock
                ]
            );
        // Set default expectations used by all tests
        $this->_routeMock->expects($this->any())->method('getServiceClass')->will($this->returnValue(self::SERVICE_ID));
        $this->_routeMock->expects($this->any())->method('getServiceMethod')
            ->will($this->returnValue(self::SERVICE_METHOD));
        $routerMock->expects($this->any())->method('match')->will($this->returnValue($this->_routeMock));
        $objectManagerMock->expects($this->any())->method('get')->will($this->returnValue($this->_serviceMock));
        $this->_responseMock->expects($this->any())->method('prepareResponse')->will($this->returnValue([]));
        $this->_serviceMock->expects($this->any())->method(self::SERVICE_METHOD)->will($this->returnValue(null));
        $dataObjectProcessorMock->expects($this->any())->method('getMethodReturnType')
            ->with(self::SERVICE_ID, self::SERVICE_METHOD)
            ->will($this->returnValue('null'));
        $paramsOverriderMock->expects($this->any())->method('overrideParams')->will($this->returnValue([]));

        parent::setUp();
    }

    public function testDispatchSchemaRequest()
    {
        $params = [
            \Magento\Framework\Webapi\Request::REQUEST_PARAM_SERVICES => 'foo',
        ];
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn(\Magento\Webapi\Controller\Rest::SCHEMA_PATH);
        $this->_requestMock->expects($this->any())
            ->method('getParams')
            ->will($this->returnValue($params));
        $schema = 'Some REST schema content';
        $this->swaggerGeneratorMock->expects($this->any())->method('generate')->willReturn($schema);

        /** @var \Magento\Framework\App\ResponseInterface $response */
        $response = $this->_restController->dispatch($this->_requestMock);
        $this->assertEquals($schema, $response->getBody());
    }

    public function testDispatchAllSchemaRequest()
    {
        $params = [
            \Magento\Framework\Webapi\Request::REQUEST_PARAM_SERVICES => 'all',
        ];
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn(\Magento\Webapi\Controller\Rest::SCHEMA_PATH);
        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        [\Magento\Framework\Webapi\Request::REQUEST_PARAM_SERVICES, null, 'all']
                    ]
                )
            );
        $this->_requestMock->expects($this->any())
            ->method('getParams')
            ->will($this->returnValue($params));
        $this->_requestMock->expects($this->any())
            ->method('getRequestedServices')
            ->will($this->returnValue('all'));
        $schema = 'Some REST schema content';
        $this->swaggerGeneratorMock->expects($this->any())->method('generate')->willReturn($schema);
        $this->swaggerGeneratorMock->expects($this->once())->method('getListOfServices')
            ->willReturn(['listOfServices']);

        $this->_restController->dispatch($this->_requestMock);
        $this->assertEquals($schema, $this->_responseMock->getBody());
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

    public function testGetMethodAllStoresInvalid()
    {
        $this->_routeMock->expects($this->any())->method('getAclResources')->will($this->returnValue(['1']));
        $this->_authorizationMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $this->storeMock->expects($this->once())->method('getCode')->willReturn('admin');
        $this->_requestMock->expects($this->once())->method('getMethod')->willReturn('get');

        $this->_restController->dispatch($this->_requestMock);

        $this->assertTrue($this->_responseMock->isException());
        $this->assertSame(
            "Cannot perform GET operation with store code 'all'",
            $this->_responseMock->getException()[0]->getMessage()
        );
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
