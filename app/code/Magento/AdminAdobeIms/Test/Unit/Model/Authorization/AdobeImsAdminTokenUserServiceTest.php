<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Model\Authorization;

use Magento\AdminAdobeIms\Api\SaveImsUserInterface;
use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
use Magento\AdminAdobeIms\Model\Authorization\AdobeImsAdminTokenUserService;
use Magento\AdminAdobeIms\Service\AdminLoginProcessService;
use Magento\AdminAdobeIms\Service\AdminReauthProcessService;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterfaceFactory;
use Magento\AdobeImsApi\Api\GetProfileInterface;
use Magento\AdobeImsApi\Api\GetTokenInterface;
use Magento\AdobeImsApi\Api\OrganizationMembershipInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AuthenticationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\AdminAdobeIms\Model\Authorization\AdobeImsAdminTokenUserService
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdobeImsAdminTokenUserServiceTest extends TestCase
{
    private const CODE = 'Test Code';

    /**
     * @var AdobeImsAdminTokenUserService
     */
    protected $adobeImsAdminTokenUserService;

    /**
     * @var ImsConfig
     */
    private $adminImsConfigMock;

    /**
     * @var GetTokenInterface
     */
    private $token;

    /**
     * @var GetProfileInterface
     */
    private $profile;

    /**
     * @var OrganizationMembershipInterface
     */
    private $organizationMembership;

    /**
     * @var AdminLoginProcessService
     */
    private $adminLoginProcessService;

    /**
     * @var RequestInterface
     */
    private $requestInterfaceMock;

    /**
     * @var AdminReauthProcessService
     */
    private $adminReauthProcessService;

    /**
     * @var TokenResponseInterfaceFactory
     */
    private $tokenResponseFactoryMock;

    /**
     * @var SaveImsUserInterface
     */
    private $saveImsUser;

    protected function setUp(): void
    {
        $this->adminImsConfigMock = $this->createMock(ImsConfig::class);
        $this->token = $this->createMock(GetTokenInterface::class);
        $this->profile = $this->createMock(GetProfileInterface::class);
        $this->organizationMembership = $this->createMock(OrganizationMembershipInterface::class);
        $this->adminLoginProcessService = $this->createMock(AdminLoginProcessService::class);
        $this->requestInterfaceMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getHeader','getParam'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->adminReauthProcessService = $this->createMock(AdminReauthProcessService::class);
        $this->tokenResponseFactoryMock = $this->createMock(TokenResponseInterfaceFactory::class);
        $this->saveImsUser = $this->createMock(SaveImsUserInterface::class);
        $this->adminImsConfigMock->expects($this->any())
            ->method('enabled')
            ->willReturn(true);

        $this->adobeImsAdminTokenUserService = new AdobeImsAdminTokenUserService(
            $this->adminImsConfigMock,
            $this->organizationMembership,
            $this->adminLoginProcessService,
            $this->adminReauthProcessService,
            $this->requestInterfaceMock,
            $this->token,
            $this->profile,
            $this->tokenResponseFactoryMock,
            $this->saveImsUser
        );
    }

    /**
     * Test Process Login Request
     *
     * @return void
     * @param array $responseData
     * @dataProvider responseDataProvider
     */
    public function testProcessLoginRequest(array $responseData): void
    {
        $this->requestInterfaceMock->expects($this->exactly(2))
            ->method('getParam')->with('code')->willReturn(self::CODE);

        $this->requestInterfaceMock->expects($this->once())
            ->method('getModuleName')->willReturn('adobe_ims_auth');

        $tokenResponse = $this->createMock(TokenResponseInterface::class);
        $tokenResponse->expects($this->any())
            ->method('getAccessToken')
            ->willReturn($responseData['access_token']);

        $this->token->expects($this->once())
            ->method('getTokenResponse')
            ->with(self::CODE)
            ->willReturn($tokenResponse);

        $this->profile->expects($this->once())
            ->method('getProfile')
            ->with($responseData['access_token'])
            ->willReturn($responseData);

        $this->organizationMembership->expects($this->once())
            ->method('checkOrganizationMembership')
            ->with($responseData['access_token']);

        $this->saveImsUser->expects($this->once())
            ->method('save')
            ->with($responseData);

        $this->adminLoginProcessService->expects($this->once())
            ->method('execute')
            ->with($tokenResponse, $responseData);

        $this->adobeImsAdminTokenUserService->processLoginRequest();
    }

    /**
     * Test Process Login Request
     *
     * @return void
     * @param array $responseData
     * @dataProvider responseDataProvider
     */
    public function testProcessLoginRequestWithAuthorizationHeader(array $responseData): void
    {
        $this->requestInterfaceMock->expects($this->once())
            ->method('getModuleName')->willReturn('adobe_ims_auth');

        $this->requestInterfaceMock->expects($this->exactly(2))
            ->method('getHeader')
            ->with('Authorization')
            ->willReturn('Bearer kladjflakdjf3423rfzddsf');

        $data = ['access_token' => 'kladjflakdjf3423rfzddsf'];

        $tokenResponse = $this->createMock(TokenResponseInterface::class);
        $this->tokenResponseFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $data])
            ->willReturn($tokenResponse);

        $tokenResponse->expects($this->any())
            ->method('getAccessToken')
            ->willReturn($responseData['access_token']);

        $this->profile->expects($this->once())
            ->method('getProfile')
            ->with($data['access_token'])
            ->willReturn($responseData);

        $this->organizationMembership->expects($this->once())
            ->method('checkOrganizationMembership')
            ->with($responseData['access_token']);

        $this->saveImsUser->expects($this->once())
            ->method('save')
            ->with($responseData);

        $this->adminLoginProcessService->expects($this->once())
            ->method('execute')
            ->with($tokenResponse, $responseData);

        $this->adobeImsAdminTokenUserService->processLoginRequest();
    }

    /**
     * Test exception when tried to access from other module
     *
     * @return void
     * @throws AuthenticationException
     */
    public function testExceptionWhenTriedToAccessFromOtherModule(): void
    {
        $this->requestInterfaceMock->expects($this->once())
            ->method('getModuleName')->willReturn('Test Module');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('An authentication error occurred. Verify and try again.');

        $this->adobeImsAdminTokenUserService->processLoginRequest();
    }

    /**
     * Test exception when profile not found
     *
     * @return void
     * @param array $responseData
     * @dataProvider responseDataProvider
     * @throws AuthenticationException
     */
    public function testExceptionWhenProfileNotFoundBasedOnAccessToken(array $responseData): void
    {
        $this->requestInterfaceMock->expects($this->exactly(2))
            ->method('getParam')->with('code')->willReturn(self::CODE);

        $this->requestInterfaceMock->expects($this->once())
            ->method('getModuleName')->willReturn('adobe_ims_auth');

        $tokenResponse = $this->createMock(TokenResponseInterface::class);
        $tokenResponse->expects($this->any())
            ->method('getAccessToken')
            ->willReturn($responseData['access_token']);

        $this->token->expects($this->once())
            ->method('getTokenResponse')
            ->with(self::CODE)
            ->willReturn($tokenResponse);

        $this->profile->expects($this->once())
            ->method('getProfile')
            ->with($responseData['access_token'])
            ->willReturn('');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('An authentication error occurred. Verify and try again.');

        $this->adobeImsAdminTokenUserService->processLoginRequest();
    }

    /**
     * Test exception when admin login provided with wrong info
     *
     * @return void
     * @param array $responseData
     * @dataProvider responseDataProvider
     * @throws AdobeImsAuthorizationException
     */
    public function testExceptionWhenAdminLoginProcessCalledWithWrongInfo(array $responseData): void
    {
        $this->requestInterfaceMock->expects($this->exactly(2))
            ->method('getParam')->with('code')->willReturn(self::CODE);

        $this->requestInterfaceMock->expects($this->once())
            ->method('getModuleName')->willReturn('adobe_ims_auth');

        $tokenResponse = $this->createMock(TokenResponseInterface::class);
        $tokenResponse->expects($this->any())
            ->method('getAccessToken')
            ->willReturn($responseData['access_token']);

        $this->token->expects($this->once())
            ->method('getTokenResponse')
            ->with(self::CODE)
            ->willReturn($tokenResponse);

        $this->profile->expects($this->once())
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
                    'tokenResponse' => [
                        'name' => 'Test User',
                        'email' => 'user@test.com',
                        'access_token' => 'kladjflakdjf3423rfzddsf',
                        'refresh_token' => 'kladjflakdjf3423rfzddsf',
                        'expires_in' => 1642259230998,
                        'first_name' => 'Test',
                        'last_name' => 'User'
                    ]
                ]
            ];
    }
}
