<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\AdminSessionInfo;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\Plugin\AuthSession;
use Magento\Security\Model\SecurityCookie;
use Magento\Security\Model\UserExpirationManager;
use Magento\User\Model\User;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\Plugin\AuthSession testing
 */
class AuthSessionTest extends TestCase
{
    /** @var  AuthSession */
    protected $model;

    /** @var RequestInterface */
    protected $requestMock;

    /** @var ManagerInterface */
    protected $messageManagerMock;

    /** @var AdminSessionsManager */
    protected $adminSessionsManagerMock;

    /** @var SecurityCookie */
    protected $securityCookieMock;

    /** @var Session */
    protected $authSessionMock;

    /** @var AdminSessionInfo */
    protected $currentSessionMock;

    /** @var  ObjectManager */
    protected $objectManager;

    /**@var \Magento\Security\Model\UserExpirationManager */
    protected $userExpirationManagerMock;

    /**@var \Magento\User\Model\User */
    protected $userMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            ['getParam', 'getModuleName', 'getActionName'],
            '',
            false
        );

        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->adminSessionsManagerMock = $this->createPartialMock(
            AdminSessionsManager::class,
            ['getCurrentSession', 'processProlong', 'getLogoutReasonMessage']
        );

        $this->securityCookieMock = $this->createPartialMock(SecurityCookie::class, ['setLogoutReasonCookie']);

        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getUser'])
            ->onlyMethods(['destroy'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->currentSessionMock = $this->getMockBuilder(AdminSessionInfo::class)
            ->addMethods(['getStatus', 'isActive'])
            ->onlyMethods(['isLoggedInStatus'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->userExpirationManagerMock = $this->createPartialMock(
            UserExpirationManager::class,
            ['isUserExpired', 'deactivateExpiredUsersById']
        );

        $this->userMock = $this->createMock(User::class);

        $this->model = $this->objectManager->getObject(
            AuthSession::class,
            [
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'sessionsManager' => $this->adminSessionsManagerMock,
                'securityCookie' => $this->securityCookieMock,
                'userExpirationManager' => $this->userExpirationManagerMock,
            ]
        );

        $this->adminSessionsManagerMock->expects($this->any())
            ->method('getCurrentSession')
            ->willReturn($this->currentSessionMock);
    }

    /**
     * @return void
     */
    public function testAroundProlongSessionIsNotActiveAndIsNotAjaxRequest()
    {
        $result = 'result';
        $errorMessage = 'Error Message';

        $proceed = function () use ($result) {
            return $result;
        };

        $this->currentSessionMock->expects($this->once())
            ->method('isLoggedInStatus')
            ->willReturn(false);

        $this->authSessionMock->expects($this->once())
            ->method('destroy');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(false);

        $this->adminSessionsManagerMock->expects($this->once())
            ->method('getLogoutReasonMessage')
            ->willReturn($errorMessage);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($errorMessage);

        $this->model->aroundProlong($this->authSessionMock, $proceed);
    }

    /**
     * @return void
     */
    public function testAroundProlongSessionIsNotActiveAndIsAjaxRequest()
    {
        $result = 'result';
        $status = 1;

        $proceed = function () use ($result) {
            return $result;
        };

        $this->currentSessionMock->expects($this->any())
            ->method('isActive')
            ->willReturn(false);

        $this->authSessionMock->expects($this->once())
            ->method('destroy');

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(true);

        $this->currentSessionMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);

        $this->securityCookieMock->expects($this->once())
            ->method('setLogoutReasonCookie')
            ->with($status)
            ->willReturnSelf();

        $this->model->aroundProlong($this->authSessionMock, $proceed);
    }

    /**
     * @return void
     */
    public function testAroundProlongSessionIsActiveUserIsExpired()
    {
        $result = 'result';
        $errorMessage = 'Error Message';

        $proceed = function () use ($result) {
            return $result;
        };

        $adminUserId = '12345';
        $this->currentSessionMock->expects($this->once())
            ->method('isLoggedInStatus')
            ->willReturn(true);

        $this->authSessionMock->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($this->userMock);

        $this->userMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($adminUserId);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(false);

        $this->userExpirationManagerMock->expects($this->once())
            ->method('isUserExpired')
            ->with($adminUserId)
            ->willReturn(true);

        $this->userExpirationManagerMock->expects($this->once())
            ->method('deactivateExpiredUsersById')
            ->with([$adminUserId]);

        $this->authSessionMock->expects($this->once())
            ->method('destroy');

        $this->adminSessionsManagerMock->expects($this->once())
            ->method('getLogoutReasonMessage')
            ->willReturn($errorMessage);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($errorMessage);

        $this->model->aroundProlong($this->authSessionMock, $proceed);
    }

    /**
     * @return void
     */
    public function testAroundProlongSessionIsActive()
    {
        $result = 'result';
        $proceed = function () use ($result) {
            return $result;
        };

        $adminUserId = '12345';
        $this->currentSessionMock->expects($this->any())
            ->method('isLoggedInStatus')
            ->willReturn(true);

        $this->authSessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock);

        $this->userMock->expects($this->once())
            ->method('getId')
            ->willReturn($adminUserId);

        $this->userExpirationManagerMock->expects($this->once())
            ->method('isUserExpired')
            ->with($adminUserId)
            ->willReturn(false);

        $this->adminSessionsManagerMock->expects($this->any())
            ->method('processProlong');

        $this->assertEquals($result, $this->model->aroundProlong($this->authSessionMock, $proceed));
    }
}
