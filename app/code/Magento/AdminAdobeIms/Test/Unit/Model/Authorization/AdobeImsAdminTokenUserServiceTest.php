<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Model\Authorization;

use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
use Magento\AdminAdobeIms\Model\Authorization\AdobeImsAdminTokenUserService;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Service\AdminLoginProcessService;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdminAdobeIms\Service\ImsOrganizationService;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\AdminAdobeIms\Model\Authorization\AdobeImsAdminTokenUserService
 */
class AdobeImsAdminTokenUserServiceTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var AdobeImsAdminTokenUserService
     */
    protected $adobeImsAdminTokenUserService;

    /**
     * @var ImsConfig
     */
    private $adminImsConfigMock;

    /**
     * @var ImsConnection
     */
    private $adminImsConnectionMock;

    /**
     * @var ImsOrganizationService
     */
    private $adminOrganizationService;

    /**
     * @var AdminLoginProcessService
     */
    private $adminLoginProcessService;

    /**
     * @var RequestInterface
     */
    private $requestInterfaceMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->adminImsConfigMock = $this->createMock(ImsConfig::class);
        $this->adminImsConnectionMock = $this->createMock(ImsConnection::class);
        $this->adminOrganizationService = $this->createMock(ImsOrganizationService::class);
        $this->adminLoginProcessService = $this->createMock(AdminLoginProcessService::class);
        $this->requestInterfaceMock = $this->createMock(RequestInterface::class);

        $this->adminImsConfigMock->expects($this->any())
            ->method('enabled')
            ->willReturn(true);

        $this->adobeImsAdminTokenUserService = $this->objectManager->getObject(
            AdobeImsAdminTokenUserService::class,
            [
                'adminImsConfig' => $this->adminImsConfigMock,
                'adminImsConnection' => $this->adminImsConnectionMock,
                'adminOrganizationService' => $this->adminOrganizationService,
                'adminLoginProcessService' => $this->adminLoginProcessService,
                'request' => $this->requestInterfaceMock,
            ]
        );
    }

    /**
     * Test Process Login Request
     *
     * @return void
     * @param string $code
     * @param array $responseData
     * @dataProvider responseDataProvider
     */
    public function testProcessLoginRequest(string $code, array $responseData)
    {
        $this->requestInterfaceMock->expects($this->exactly(2))
            ->method('getParam')->with('code')->willReturn($code);

        $this->requestInterfaceMock->expects($this->once())
            ->method('getModuleName')->willReturn('adobe_ims_auth');

        $tokenResponse = $this->createMock(TokenResponseInterface::class);
        $tokenResponse->expects($this->any())
            ->method('getAccessToken')
            ->willReturn($responseData['access_token']);

        $this->adminImsConnectionMock->expects($this->once())
            ->method('getTokenResponse')
            ->with($code)
            ->willReturn($tokenResponse);

        $this->adminImsConnectionMock->expects($this->once())
            ->method('getProfile')
            ->with($responseData['access_token'])
            ->willReturn($responseData);

        $this->adminOrganizationService->expects($this->once())
            ->method('checkOrganizationMembership')
            ->with($responseData['access_token']);

        $this->adobeImsAdminTokenUserService->processLoginRequest();
    }

    /**
     * @return void
     * @param string $code
     * @dataProvider responseDataProvider
     * @throws AuthenticationException
     */
    public function testExceptionWhenTriedToAccessFromOtherModule(string $code): void
    {
        $this->requestInterfaceMock->expects($this->once())
            ->method('getParam')->with('code')->willReturn($code);

        $this->requestInterfaceMock->expects($this->once())
            ->method('getModuleName')->willReturn('Test Module');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('An authentication error occurred. Verify and try again.');

        $this->adobeImsAdminTokenUserService->processLoginRequest();
    }

    /**
     * @return void
     * @param string $code
     * @param array $responseData
     * @dataProvider responseDataProvider
     * @throws AuthenticationException
     */
    public function testExceptionWhenProfileNotFoundBasedOnAccessToken(
        string $code,
        array $responseData
    ): void {
        $this->requestInterfaceMock->expects($this->exactly(2))
            ->method('getParam')->with('code')->willReturn($code);

        $this->requestInterfaceMock->expects($this->once())
            ->method('getModuleName')->willReturn('adobe_ims_auth');

        $tokenResponse = $this->createMock(TokenResponseInterface::class);
        $tokenResponse->expects($this->any())
            ->method('getAccessToken')
            ->willReturn($responseData['access_token']);

        $this->adminImsConnectionMock->expects($this->once())
            ->method('getTokenResponse')
            ->with($code)
            ->willReturn($tokenResponse);

        $this->adminImsConnectionMock->expects($this->once())
            ->method('getProfile')
            ->with($responseData['access_token'])
            ->willReturn('');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('An authentication error occurred. Verify and try again.');

        $this->adobeImsAdminTokenUserService->processLoginRequest();
    }

    /**
     * @return void
     * @param string $code
     * @param array $responseData
     * @dataProvider responseDataProvider
     * @throws AdobeImsAuthorizationException
     */
    public function testExceptionWhenAdminLoginProcessCalledWithWrongInfo(
        string $code,
        array $responseData
    ): void {
        $this->requestInterfaceMock->expects($this->exactly(2))
            ->method('getParam')->with('code')->willReturn($code);

        $this->requestInterfaceMock->expects($this->once())
            ->method('getModuleName')->willReturn('adobe_ims_auth');

        $tokenResponse = $this->createMock(TokenResponseInterface::class);
        $tokenResponse->expects($this->any())
            ->method('getAccessToken')
            ->willReturn($responseData['access_token']);

        $this->adminImsConnectionMock->expects($this->once())
            ->method('getTokenResponse')
            ->with($code)
            ->willReturn($tokenResponse);

        $this->adminImsConnectionMock->expects($this->once())
            ->method('getProfile')
            ->with($responseData['access_token'])
            ->willReturn($responseData);

        $this->adminLoginProcessService->expects($this->once())
            ->method('execute')
            ->with($tokenResponse, $responseData)
            ->willThrowException(new AdobeImsAuthorizationException(
                __('You don\'t have access to this Commerce instance')
            ));

        $this->expectException(AdobeImsAuthorizationException::class);
        $this->expectExceptionMessage('You don\'t have access to this Commerce instance');

        $this->adobeImsAdminTokenUserService->processLoginRequest();
    }

    /**
     * Data provider for response.
     *
     * @return array
     */
    public function responseDataProvider(): array
    {
        return
            [
                [
                    'code' => 'Test Code',
                    'tokenResponse' => [
                        'name' => 'Test User',
                        'email' => 'user@test.com',
                        'access_token' => 'kladjflakdjf3423rfzddsf',
                        'refresh_token' => 'kladjflakdjf3423rfzddsf',
                        'expires_in' => 1642259230998
                    ]
                ]
            ];
    }
}
