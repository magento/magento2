<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Model\Authorization;

use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Model\Authorization\AdobeImsAdminTokenUserContext;
use Magento\AdminAdobeIms\Model\Authorization\AdobeImsAdminTokenUserService;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\AdminAdobeIms\Model\Authorization\AdobeImsAdminTokenUserContext
 */
class AdobeImsAdminTokenUserContextTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var AdobeImsAdminTokenUserContext
     */
    protected $adobeImsAdminTokenUserContext;

    /**
     * @var Session
     */
    protected $adminSession;

    /**
     * @var ImsConfig
     */
    private $adminImsConfigMock;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var ImsConnection
     */
    private $adminImsConnectionMock;

    /**
     * @var AdobeImsAdminTokenUserService
     */
    private $adminTokenUserService;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->adminSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'getId','getAdobeAccessToken'])
            ->getMock();

        $this->adminImsConfigMock = $this->createMock(ImsConfig::class);
        $this->auth = $this->createMock(Auth::class);
        $this->adminImsConnectionMock = $this->createMock(ImsConnection::class);
        $this->adminTokenUserService = $this->createMock(AdobeImsAdminTokenUserService::class);
        $this->auth
            ->method('getAuthStorage')
            ->willReturn($this->adminSession);

        $this->adminImsConfigMock->expects($this->any())
            ->method('enabled')
            ->willReturn(true);

        $this->adobeImsAdminTokenUserContext = $this->objectManager->getObject(
            AdobeImsAdminTokenUserContext::class,
            [
                'adminImsConfig' => $this->adminImsConfigMock,
                'auth' => $this->auth,
                'adminImsConnection' => $this->adminImsConnectionMock,
                'adminTokenUserService' => $this->adminTokenUserService,
            ]
        );
    }

    public function testGetUserId()
    {
        $userId = 1;

        $this->setupUserId($userId);

        $this->assertEquals($userId, $this->adobeImsAdminTokenUserContext->getUserId());
    }

    /**
     * @return void
     * @throws AuthenticationException
     */
    public function testExceptionWhenAccessTokenNotValid(): void
    {
        $this->adminSession->expects($this->any())
            ->method('getAdobeAccessToken')
            ->willReturn('test');

        $this->adminImsConnectionMock
            ->expects($this->once())
            ->method('validateToken')
            ->willReturn(false);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('An authentication error occurred. Verify and try again.');

        $this->adobeImsAdminTokenUserContext->getUserId();
    }

    public function testGetUserType()
    {
        $this->assertEquals(UserContextInterface::USER_TYPE_ADMIN, $this->adobeImsAdminTokenUserContext->getUserType());
    }

    /**
     * @param int|null $userId
     * @return void
     */
    public function setupUserId($userId)
    {
        $this->adminSession->expects($this->any())
            ->method('getAdobeAccessToken')
            ->willReturn(null);

        if ($userId) {
            $userMock = $this->getMockBuilder(User::class)
                ->disableOriginalConstructor()
                ->setMethods(['getUserId'])
                ->getMock();

            $userMock->expects($this->once())
                ->method('getUserId')
                ->willReturn($userId);

            $this->adminSession->expects($this->once())
                ->method('getUser')
                ->willReturn($userMock);
        }
    }
}
