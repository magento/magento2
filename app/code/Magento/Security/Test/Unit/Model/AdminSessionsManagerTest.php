<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\AdminSessionInfo;
use Magento\Security\Model\AdminSessionInfoFactory;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\ConfigInterface;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory;
use Magento\User\Model\User;
use PHPUnit\Framework\TestCase;

/**
 * Test class for AdminSessionsManager testing
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminSessionsManagerTest extends TestCase
{
    /** @var  AdminSessionsManager */
    protected $model;

    /** @var AdminSessionInfo */
    protected $currentSessionMock;

    /** @var Session */
    protected $authSessionMock;

    /** @var ConfigInterface */
    protected $securityConfigMock;

    /** @var User */
    protected $userMock;

    /** @var CollectionFactory */
    protected $adminSessionInfoCollectionFactoryMock;

    /** @var Collection */
    protected $adminSessionInfoCollectionMock;

    /** @var AdminSessionInfoFactory */
    protected $adminSessionInfoFactoryMock;

    /**
     * @var DateTime
     */
    protected $dateTimeMock;

    /** @var  ObjectManager */
    protected $objectManager;

    /** @var RemoteAddress */
    protected $remoteAddressMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods([
                'isActive',
                'getStatus',
                'getUser',
                'getId',
                'getUpdatedAt',
                'getAdminSessionInfoId',
                'setAdminSessionInfoId'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->adminSessionInfoCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->adminSessionInfoCollectionMock = $this->createPartialMock(
            Collection::class,
            [
                'filterByUser',
                'filterExpiredSessions',
                'loadData',
                'setDataToAll',
                'save',
                'updateActiveSessionsStatus',
                'deleteSessionsOlderThen'
            ]
        );

        $this->adminSessionInfoFactoryMock = $this->createPartialMock(
            AdminSessionInfoFactory::class,
            ['create']
        );

        $this->currentSessionMock = $this->getMockBuilder(AdminSessionInfo::class)
            ->addMethods(['isActive', 'getStatus', 'getUserId', 'getUpdatedAt'])
            ->onlyMethods(['load', 'setData', 'setIsOtherSessionsTerminated', 'save', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->securityConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->userMock = $this->createPartialMock(User::class, ['getId']);

        $this->dateTimeMock =  $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->remoteAddressMock =  $this->getMockBuilder(RemoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            AdminSessionsManager::class,
            [
                'securityConfig' => $this->securityConfigMock,
                'authSession' => $this->authSessionMock,
                'adminSessionInfoFactory' => $this->adminSessionInfoFactoryMock,
                'adminSessionInfoCollectionFactory' => $this->adminSessionInfoCollectionFactoryMock,
                'dateTime' => $this->dateTimeMock,
                'remoteAddress' => $this->remoteAddressMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testProcessLogin()
    {
        $useId = 1;
        $sessionLifetime = 100;
        $ip = 12345;
        $timestamp = time();

        $olderThen = $timestamp - $sessionLifetime;
        $adminSessionInfoId = 50;
        $this->authSessionMock->expects($this->any())
            ->method('getAdminSessionInfoId')
            ->willReturn($adminSessionInfoId);

        $this->adminSessionInfoFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->currentSessionMock);

        $this->authSessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock);
        $this->userMock->expects($this->once())
            ->method('getId')
            ->willReturn($useId);

        $this->remoteAddressMock->expects($this->once())
            ->method('getRemoteAddress')
            ->willReturn($ip);

        $this->currentSessionMock->expects($this->once())
            ->method('setData')
            ->willReturnSelf();

        $this->currentSessionMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->dateTimeMock->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($timestamp);

        $this->securityConfigMock->expects($this->once())
            ->method('getAdminSessionLifetime')
            ->willReturn($sessionLifetime);

        $this->securityConfigMock->expects($this->once())
            ->method('isAdminAccountSharingEnabled')
            ->willReturn(0);

        $this->currentSessionMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($useId);

        $this->currentSessionMock->expects($this->any())
            ->method('getId')
            ->willReturn($adminSessionInfoId);

        $this->adminSessionInfoCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->adminSessionInfoCollectionMock);

        $this->adminSessionInfoCollectionMock->expects($this->once())->method('updateActiveSessionsStatus')
            ->with(
                AdminSessionInfo::LOGGED_OUT_BY_LOGIN,
                $useId,
                $adminSessionInfoId,
                $olderThen
            )
            ->willReturn(1);

        $this->currentSessionMock->expects($this->once())
            ->method('setIsOtherSessionsTerminated')
            ->with(true)
            ->willReturnSelf();

        $this->model->processLogin();
    }

    /**
     * @return void
     */
    public function testProcessProlong()
    {
        $lastUpdatedAt = '2015-12-31 23:59:59';
        $newUpdatedAt = '2016-01-01 00:00:30';
        $adminSessionInfoId = 50;
        $this->authSessionMock->expects($this->any())
            ->method('getAdminSessionInfoId')
            ->willReturn($adminSessionInfoId);

        $this->adminSessionInfoFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSessionMock);

        $this->currentSessionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->currentSessionMock->expects($this->once())
            ->method('getUpdatedAt')
            ->willReturn($lastUpdatedAt);

        $this->authSessionMock->expects($this->exactly(2))
            ->method('getUpdatedAt')
            ->willReturn(strtotime($newUpdatedAt));

        $this->securityConfigMock->expects($this->once())
            ->method('getAdminSessionLifetime')
            ->willReturn(100);

        $this->currentSessionMock->expects($this->once())
            ->method('setData')
            ->with('updated_at', $newUpdatedAt)
            ->willReturnSelf();

        $this->currentSessionMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->model->processProlong();
    }

    /**
     * @return void
     */
    public function testUpdatedAtIsNull()
    {
        $newUpdatedAt = '2016-01-01 00:00:30';
        $adminSessionInfoId = 50;
        $this->authSessionMock->expects($this->any())
            ->method('getAdminSessionInfoId')
            ->willReturn($adminSessionInfoId);

        $this->adminSessionInfoFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSessionMock);

        $this->currentSessionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->currentSessionMock->expects($this->once())
            ->method('getUpdatedAt')
            ->willReturn(null);

        $this->authSessionMock->expects($this->once())
            ->method('getUpdatedAt')
            ->willReturn(strtotime($newUpdatedAt));

        $this->securityConfigMock->expects($this->once())
            ->method('getAdminSessionLifetime')
            ->willReturn(100);

        $this->currentSessionMock->expects($this->never())
            ->method('setData')
            ->willReturnSelf();

        $this->currentSessionMock->expects($this->never())
            ->method('save')
            ->willReturnSelf();

        $this->model->processProlong();
    }

    /**
     * @return void
     */
    public function testProcessLogout()
    {
        $adminSessionInfoId = 50;
        $this->authSessionMock->expects($this->any())
            ->method('getAdminSessionInfoId')
            ->willReturn($adminSessionInfoId);

        $this->adminSessionInfoFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSessionMock);

        $this->currentSessionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->currentSessionMock->expects($this->once())
            ->method('setData')
            ->with('status', AdminSessionInfo::LOGGED_OUT)
            ->willReturnSelf();

        $this->currentSessionMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->model->processLogout();
    }

    /**
     * @return void
     */
    public function testGetCurrentSession()
    {
        $adminSessionInfoId = 50;
        $this->authSessionMock->expects($this->any())
            ->method('getAdminSessionInfoId')
            ->willReturn($adminSessionInfoId);

        $this->adminSessionInfoFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSessionMock);

        $this->currentSessionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->assertEquals($this->currentSessionMock, $this->model->getCurrentSession());
    }

    /**
     * @return void
     */
    public function testCleanExpiredSessions()
    {
        $timestamp = time();

        $this->adminSessionInfoCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->adminSessionInfoCollectionMock);

        $this->dateTimeMock->expects($this->once())
            ->method('gmtTimestamp')
            ->willReturn($timestamp);

        $this->adminSessionInfoCollectionMock->expects($this->once())->method('deleteSessionsOlderThen')
            ->with($timestamp - AdminSessionsManager::ADMIN_SESSION_LIFETIME)
            ->willReturnSelf();

        $this->model->cleanExpiredSessions();
    }

    /**
     * @param string $expectedResult
     * @param int $sessionStatus
     * @dataProvider dataProviderLogoutReasonMessage
     */
    public function testGetLogoutReasonMessage($expectedResult, $sessionStatus)
    {
        $this->adminSessionInfoFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->currentSessionMock);
        $this->authSessionMock->expects($this->any())
            ->method('getUser')
            ->willReturn($this->userMock);
        $this->currentSessionMock->expects($this->once())
            ->method('setData')
            ->willReturn($this->currentSessionMock);
        $this->currentSessionMock->expects($this->once())
            ->method('save')
            ->willReturn($this->currentSessionMock);
        $this->adminSessionInfoCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->adminSessionInfoCollectionMock);
        $this->adminSessionInfoCollectionMock->expects($this->once())->method('filterByUser')
            ->willReturnSelf();
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('filterExpiredSessions')
            ->willReturnSelf();
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('loadData')
            ->willReturnSelf();
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('setDataToAll')
            ->willReturnSelf();
        $this->currentSessionMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($sessionStatus);

        $this->assertEquals($expectedResult, $this->model->getLogoutReasonMessage());
    }

    /**
     * @return array
     */
    public function dataProviderLogoutReasonMessage()
    {
        return [
            [
                'expectedResult' => __(
                    'Someone logged into this account from another device or browser.'
                    . ' Your current session is terminated.'
                ),
                'sessionStatus' => AdminSessionInfo::LOGGED_OUT_BY_LOGIN
            ],
            [
                'expectedResult' => __('Your current session is terminated by another user of this account.'),
                'sessionStatus' => AdminSessionInfo::LOGGED_OUT_MANUALLY
            ],
            [
                'expectedResult' => __('Your current session has been expired.'),
                'sessionStatus' => AdminSessionInfo::LOGGED_OUT
            ],
            [
                'expectedResult' => __('Your account is temporarily disabled. Please try again later.'),
                'sessionStatus' => AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            ],
            [
                'expectedResult' => '',
                'sessionStatus' => AdminSessionInfo::LOGGED_IN
            ]
        ];
    }

    /**
     * @return void
     */
    public function testGetSessionsForCurrentUser()
    {
        $useId = 1;
        $sessionLifetime = 100;
        $this->adminSessionInfoCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->adminSessionInfoCollectionMock);
        $this->authSessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock);
        $this->userMock->expects($this->once())
            ->method('getId')
            ->willReturn($useId);
        $this->adminSessionInfoCollectionMock->expects($this->once())->method('filterByUser')
            ->with($useId, AdminSessionInfo::LOGGED_IN)
            ->willReturnSelf();
        $this->securityConfigMock->expects($this->once())
            ->method('getAdminSessionLifetime')
            ->willReturn($sessionLifetime);
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('filterExpiredSessions')
            ->with($sessionLifetime)
            ->willReturnSelf();
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('loadData')
            ->willReturnSelf();

        $this->assertSame($this->adminSessionInfoCollectionMock, $this->model->getSessionsForCurrentUser());
    }

    /**
     * @return void
     */
    public function testLogoutOtherUserSessions()
    {
        $useId = 1;
        $sessionLifetime = 100;
        $adminSessionInfoId = 50;
        $this->authSessionMock->expects($this->any())
            ->method('getAdminSessionInfoId')
            ->willReturn($adminSessionInfoId);

        $this->adminSessionInfoCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->adminSessionInfoCollectionMock);
        $this->authSessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock);

        $this->userMock->expects($this->once())
            ->method('getId')
            ->willReturn($useId);
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('filterByUser')
            ->with($useId, AdminSessionInfo::LOGGED_IN, $adminSessionInfoId)
            ->willReturnSelf();
        $this->securityConfigMock->expects($this->once())
            ->method('getAdminSessionLifetime')
            ->willReturn($sessionLifetime);
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('filterExpiredSessions')
            ->with($sessionLifetime)
            ->willReturnSelf();
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('loadData')
            ->willReturnSelf();
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('setDataToAll')
            ->with($this->equalTo('status'), AdminSessionInfo::LOGGED_OUT_MANUALLY)
            ->willReturnSelf();
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('save');

        $this->model->logoutOtherUserSessions();
    }
}
