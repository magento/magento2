<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Controller\Rest;

class RequestValidatorTest extends \PHPUnit\Framework\TestCase
{
    const SERVICE_METHOD = 'testMethod';

    const SERVICE_ID = 'Magento\Webapi\Controller\Rest\TestService';

    /**
     * @var \Magento\Webapi\Controller\Rest\RequestValidator
     */
    private $requestValidator;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /** @var \Magento\Store\Model\StoreManagerInterface |\PHPUnit\Framework\MockObject\MockObject */
    private $storeManagerMock;

    /** @var \Magento\Store\Api\Data\StoreInterface |\PHPUnit\Framework\MockObject\MockObject */
    private $storeMock;

    /**
     * @var \Magento\Framework\Webapi\Authorization|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authorizationMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Webapi\Controller\Rest\Router\Route
     */
    private $routeMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\Webapi\Rest\Request::class)
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
        $routerMock = $this->getMockBuilder(\Magento\Webapi\Controller\Rest\Router::class)->setMethods(['match'])
            ->disableOriginalConstructor()->getMock();
        $this->routeMock = $this->getMockBuilder(\Magento\Webapi\Controller\Rest\Router\Route::class)
            ->setMethods(['isSecure', 'getServiceMethod', 'getServiceClass', 'getAclResources', 'getParameters'])
            ->disableOriginalConstructor()->getMock();
        $this->authorizationMock = $this->getMockBuilder(\Magento\Framework\Webapi\Authorization::class)
            ->disableOriginalConstructor()->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);

        $this->requestValidator =
            $objectManager->getObject(
                \Magento\Webapi\Controller\Rest\RequestValidator::class,
                [
                    'request' => $this->requestMock,
                    'router' => $routerMock,
                    'authorization' => $this->authorizationMock,
                    'storeManager' => $this->storeManagerMock
                ]
            );

        // Set default expectations used by all tests
        $this->routeMock->expects($this->any())->method('getServiceClass')->willReturn(self::SERVICE_ID);
        $this->routeMock->expects($this->any())->method('getServiceMethod')
            ->willReturn(self::SERVICE_METHOD);
        $routerMock->expects($this->any())->method('match')->willReturn($this->routeMock);

        parent::setUp();
    }

    /**
     * Test Secure Request and Secure route combinations
     *
     * @dataProvider dataProviderSecureRequestSecureRoute
     */
    public function testSecureRouteAndRequest($isSecureRoute, $isSecureRequest)
    {
        $this->routeMock->expects($this->any())->method('isSecure')->willReturn($isSecureRoute);
        $this->routeMock->expects($this->any())->method('getAclResources')->willReturn(['1']);
        $this->requestMock->expects($this->any())->method('getRequestData')->willReturn([]);
        $this->requestMock->expects($this->any())->method('isSecure')->willReturn($isSecureRequest);
        $this->authorizationMock->expects($this->once())->method('isAllowed')->willReturn(true);
        $this->requestValidator->validate();
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
     */
    public function testInSecureRequestOverSecureRoute()
    {
        $this->expectException(\Magento\Framework\Webapi\Exception::class);
        $this->expectExceptionMessage('Operation allowed only in HTTPS');

        $this->routeMock->expects($this->any())->method('isSecure')->willReturn(true);
        $this->routeMock->expects($this->any())->method('getAclResources')->willReturn(['1']);
        $this->requestMock->expects($this->any())->method('isSecure')->willReturn(false);
        $this->authorizationMock->expects($this->once())->method('isAllowed')->willReturn(true);

        $this->requestValidator->validate();
    }

    /**
     */
    public function testAuthorizationFailed()
    {
        $this->expectException(\Magento\Framework\Exception\AuthorizationException::class);
        $this->expectExceptionMessage('The consumer isn\'t authorized to access 5, 6.');

        $this->authorizationMock->expects($this->once())->method('isAllowed')->willReturn(false);
        $this->routeMock->expects($this->any())->method('getAclResources')->willReturn(['5', '6']);
        $this->requestValidator->validate();
    }
}
