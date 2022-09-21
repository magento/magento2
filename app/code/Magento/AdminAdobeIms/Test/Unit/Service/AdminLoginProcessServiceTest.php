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
use Magento\AdminAdobeIms\Model\User;
use Magento\AdminAdobeIms\Service\AdminLoginProcessService;
use Magento\AdobeIms\Model\LogOut;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class AdminLoginProcessServiceTest extends TestCase
{
    private const TEST_EMAIL = 'test@test.com';

    private const ERROR_MESSAGE = 'The account sign-in was incorrect or your account is disabled temporarily. '
    . 'Please wait and try again later.';

    /**
     * @var AdminLoginProcessService
     */
    private $loginService;

    /**
     * @var User
     */
    private $adminUser;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var LogOut
     */
    private $logOut;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TokenResponseInterface
     */
    private $tokenResponse;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->adminUser = $this->createMock(User::class);
        $this->logOut = $this->createMock(LogOut::class);
        $this->dateTime = $this->createMock(DateTime::class);

        $session = $this->getMockBuilder(StorageInterface::class)
            ->addMethods(['setAdobeAccessToken', 'setTokenLastCheckTime'])
            ->getMockForAbstractClass();
        $session
            ->method('setAdobeAccessToken')
            ->willReturnSelf();
        $session
            ->method('setTokenLastCheckTime')
            ->willReturnSelf();

        $this->auth = $this->createMock(Auth::class);
        $this->auth
            ->method('getAuthStorage')
            ->willReturn($session);

        $this->tokenResponse = $this->createMock(TokenResponseInterface::class);
        $this->tokenResponse
            ->method('getAccessToken')
            ->willReturn('accessToken');

        $this->loginService = $objectManagerHelper->getObject(
            AdminLoginProcessService::class,
            [
                'adminUser' => $this->adminUser,
                'auth' => $this->auth,
                'logOut' => $this->logOut,
                'dateTime' => $this->dateTime
            ]
        );
    }

    /**
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    public function testExceptionWillBeThrownWhenNoUserFound(): void
    {
        $this->adminUser
            ->method('loadByEmail')
            ->willReturn([]);

        $this->logOut
            ->expects($this->once())
            ->method('execute')
            ->with('accessToken');

        $this->expectException(AdobeImsAuthorizationException::class);
        $this->expectExceptionMessage('No matching admin user found for Adobe ID.');

        $this->loginService->execute($this->tokenResponse, ['email' => self::TEST_EMAIL]);
    }

    /**
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    public function testExceptionWillBeThrownWhenAuthenticationFails(): void
    {
        $this->adminUser
            ->method('loadByEmail')
            ->willReturn([
                'user_id' => '1',
                'username' => 'admin',
                'email' => self::TEST_EMAIL,
            ]);

        $this->auth
            ->method('loginByUsername')
            ->willThrowException(new Exception(self::ERROR_MESSAGE));

        $this->logOut
            ->expects($this->once())
            ->method('execute')
            ->with('accessToken');

        $this->expectException(AdobeImsAuthorizationException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);

        $this->loginService->execute($this->tokenResponse, ['email' => self::TEST_EMAIL]);
    }
}
