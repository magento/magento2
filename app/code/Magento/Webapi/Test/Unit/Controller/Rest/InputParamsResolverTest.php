<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Controller\Rest;

class InputParamsResolverTest extends \PHPUnit_Framework_TestCase
{
    const SERVICE_METHOD = 'testMethod';

    const SERVICE_ID = 'Magento\Webapi\Controller\Rest\TestService';

    /**
     * @var \Magento\Webapi\Controller\Rest\InputParamsResolver
     */
    private $inputParamsResolver;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /** @var \Magento\Store\Model\StoreManagerInterface |\PHPUnit_Framework_MockObject_MockObject */
    private $storeManagerMock;

    /** @var \Magento\Store\Api\Data\StoreInterface |\PHPUnit_Framework_MockObject_MockObject */
    private $storeMock;

    /**
     * @var \Magento\Framework\Webapi\ServiceInputProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceInputProcessorMock;

    /**
     * @var \Magento\Framework\Webapi\Authorization|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Webapi\Controller\Rest\Router\Route
     */
    private $routeMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\Webapi\Rest\Request')
            ->setMethods(
                [
                    'isSecure',
                    'getRequestData',
                    'getParams',
                    'getParam',
                    'getRequestedServices',
                    'getPathInfo',
                    'getHttpHost',
                    'getMethod',
                ]
            )->disableOriginalConstructor()->getMock();
        $this->requestMock->expects($this->any())
            ->method('getHttpHost')
            ->willReturn('testHostName.com');
        $routerMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\Router')->setMethods(['match'])
            ->disableOriginalConstructor()->getMock();
        $this->routeMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\Router\Route')
            ->setMethods(['isSecure', 'getServiceMethod', 'getServiceClass', 'getAclResources', 'getParameters'])
            ->disableOriginalConstructor()->getMock();
        $this->authorizationMock = $this->getMockBuilder('Magento\Framework\Webapi\Authorization')
            ->disableOriginalConstructor()->getMock();
        $paramsOverriderMock = $this->getMockBuilder('Magento\Webapi\Controller\Rest\ParamsOverrider')
            ->setMethods(['overrideParams'])
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->serviceInputProcessorMock = $this->getMockBuilder('\Magento\Framework\Webapi\ServiceInputProcessor')
            ->disableOriginalConstructor()->setMethods(['process'])->getMock();
        $this->storeMock = $this->getMock('\Magento\Store\Api\Data\StoreInterface');
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);

        $this->inputParamsResolver =
            $objectManager->getObject(
                \Magento\Webapi\Controller\Rest\InputParamsResolver::class,
                [
                    'request' => $this->requestMock,
                    'router' => $routerMock,
                    'authorization' => $this->authorizationMock,
                    'serviceInputProcessor' => $this->serviceInputProcessorMock,
                    'paramsOverrider' => $paramsOverriderMock,
                    'storeManager' => $this->storeManagerMock
                ]
            );

        // Set default expectations used by all tests
        $this->routeMock->expects($this->any())->method('getServiceClass')->will($this->returnValue(self::SERVICE_ID));
        $this->routeMock->expects($this->any())->method('getServiceMethod')
            ->will($this->returnValue(self::SERVICE_METHOD));
        $routerMock->expects($this->any())->method('match')->will($this->returnValue($this->routeMock));
        $paramsOverriderMock->expects($this->any())->method('overrideParams')->will($this->returnValue([]));

        parent::setUp();
    }

    /**
     * Test Secure Request and Secure route combinations
     *
     * @dataProvider dataProviderSecureRequestSecureRoute
     */
    public function testSecureRouteAndRequest($isSecureRoute, $isSecureRequest)
    {
        $this->routeMock->expects($this->any())->method('isSecure')->will($this->returnValue($isSecureRoute));
        $this->routeMock->expects($this->once())->method('getParameters')->will($this->returnValue([]));
        $this->routeMock->expects($this->any())->method('getAclResources')->will($this->returnValue(['1']));
        $this->requestMock->expects($this->any())->method('getRequestData')->will($this->returnValue([]));
        $this->requestMock->expects($this->any())->method('isSecure')->will($this->returnValue($isSecureRequest));
        $this->authorizationMock->expects($this->once())->method('isAllowed')->will($this->returnValue(true));
        $this->serviceInputProcessorMock->expects($this->any())->method('process')->will($this->returnValue([]));
        $this->assertInternalType('array',$this->inputParamsResolver->resolve());
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
     *
     * @expectedException \Magento\Framework\Webapi\Exception
     * @expectedExceptionMessage Operation allowed only in HTTPS
     */
    public function testInSecureRequestOverSecureRoute()
    {
        $this->routeMock->expects($this->any())->method('isSecure')->will($this->returnValue(true));
        $this->routeMock->expects($this->any())->method('getAclResources')->will($this->returnValue(['1']));
        $this->requestMock->expects($this->any())->method('isSecure')->will($this->returnValue(false));
        $this->authorizationMock->expects($this->once())->method('isAllowed')->will($this->returnValue(true));

        $this->inputParamsResolver->resolve();
    }

    /**
     * @expectedException \Magento\Framework\Exception\AuthorizationException
     * @expectedExceptionMessage Consumer is not authorized to access 5, 6
     */
    public function testAuthorizationFailed()
    {
        $this->authorizationMock->expects($this->once())->method('isAllowed')->will($this->returnValue(false));
        $this->routeMock->expects($this->any())->method('getAclResources')->will($this->returnValue(['5', '6']));
        $this->inputParamsResolver->resolve();
    }

    /**
     * @expectedException \Magento\Framework\Webapi\Exception
     * @expectedExceptionMessage Cannot perform GET operation with store code 'all'
     */
    public function testGetMethodAllStoresInvalid()
    {
        $this->routeMock->expects($this->any())->method('getAclResources')->will($this->returnValue(['1']));
        $this->authorizationMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $this->storeMock->expects($this->once())->method('getCode')->willReturn('admin');
        $this->requestMock->expects($this->once())->method('getMethod')->willReturn('get');

        $this->inputParamsResolver->resolve();
    }
}
