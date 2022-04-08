<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Service;

use Exception;
use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Model\LogOut;
use Magento\AdminAdobeIms\Model\User;
use Magento\AdminAdobeIms\Service\AdminLoginProcessService;
use Magento\AdobeIms\Model\LogIn;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class AdminLoginProcessServiceTest extends TestCase
{
    private const TEST_EMAIL = 'test@test.com';

    /**
     * @var AdminLoginProcessService
     */
    private $loginService;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var LogIn
     */
    private $logIn;

    /**
     * @var LogOut
     */
    private $logOut;

    /**
     * @var TokenResponseInterface
     */
    private $tokenResponse;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->user = $this->createMock(User::class);
        $this->auth = $this->createMock(Auth::class);
        $this->logIn = $this->createMock(LogIn::class);
        $this->logOut = $this->createMock(LogOut::class);

        $this->tokenResponse = $this->createMock(TokenResponseInterface::class);
        $this->tokenResponse
            ->method('getAccessToken')
            ->willReturn('accessToken');

        $this->loginService = $objectManagerHelper->getObject(
            AdminLoginProcessService::class,
            [
                'adminUser' => $this->user,
                'auth' => $this->auth,
                'logIn' => $this->logIn,
                'logOut' => $this->logOut,
            ]
        );
    }

    public function testExceptionWillBeThrownWhenNoUserFound(): void
    {
        $this->user
            ->method('loadByEmail')
            ->willReturn([]);

        $this->logOut
            ->expects($this->once())
            ->method('execute')
            ->with('accessToken');

        $this->expectException(AdobeImsAuthorizationException::class);
        $this->expectExceptionMessage('No matching admin user found for Adobe ID.');

        $this->loginService->execute(['email' => self::TEST_EMAIL], $this->tokenResponse);
    }

    /**
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    public function testExceptionWillBeThrownWhenLoginFails(): void
    {
        $this->user
            ->method('loadByEmail')
            ->willReturn([
                'user_id' => '1',
                'email' => self::TEST_EMAIL,
            ]);

        $this->logIn
            ->method('execute')
            ->willThrowException(new Exception());

        $this->logOut
            ->expects($this->once())
            ->method('execute')
            ->with('accessToken');

        $this->expectException(AdobeImsAuthorizationException::class);

        $this->loginService->execute(['email' => self::TEST_EMAIL], $this->tokenResponse);
    }

    /**
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    public function testExceptionWillBeThrownWhenAuthenticationFails(): void
    {
        $this->user
            ->method('loadByEmail')
            ->willReturn([
                'user_id' => '1',
                'username' => 'admin',
                'email' => self::TEST_EMAIL,
            ]);

        $this->auth
            ->method('loginByUsername')
            ->willThrowException(new Exception());

        $this->logOut
            ->expects($this->once())
            ->method('execute')
            ->with('accessToken');

        $this->expectException(AdobeImsAuthorizationException::class);

        $this->loginService->execute(['email' => self::TEST_EMAIL], $this->tokenResponse);
    }
}
