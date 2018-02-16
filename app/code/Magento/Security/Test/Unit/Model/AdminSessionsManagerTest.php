<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\ConfigInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Test class for AdminSessionsManager testing
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminSessionsManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  AdminSessionsManager */
    protected $model;

    /** @var \Magento\Security\Model\AdminSessionInfo */
    protected $currentSessionMock;

    /** @var \Magento\Backend\Model\Auth\Session */
    protected $authSessionMock;

    /** @var ConfigInterface */
    protected $securityConfigMock;

    /** @var \Magento\User\Model\User */
    protected $userMock;

    /** @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory */
    protected $adminSessionInfoCollectionFactoryMock;

    /** @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection */
    protected $adminSessionInfoCollectionMock;

    /** @var \Magento\Security\Model\AdminSessionInfoFactory */
    protected $adminSessionInfoFactoryMock;

    /**
     * @var DateTime
     */
    protected $dateTimeMock;

    /** @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /*
     * @var RemoteAddress
     */
    protected $remoteAddressMock;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->authSessionMock = $this->getMock(
            '\Magento\Backend\Model\Auth\Session',
            ['isActive', 'getStatus', 'getUser', 'getId', 'getSessionId', 'getUpdatedAt'],
            [],
            '',
            false
        );

        $this->adminSessionInfoCollectionFactoryMock = $this->getMock(
            '\Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->adminSessionInfoCollectionMock = $this->getMock(
            '\Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection',
            [
                'filterByUser',
                'filterExpiredSessions',
                'loadData',
                'setDataToAll',
                'save',
                'updateActiveSessionsStatus',
                'deleteSessionsOlderThen'
            ],
            [],
            '',
            false
        );

        $this->adminSessionInfoFactoryMock = $this->getMock(
            '\Magento\Security\Model\AdminSessionInfoFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->currentSessionMock = $this->getMock(
            '\Magento\Security\Model\AdminSessionInfo',
            [
                'isActive',
                'getStatus',
                'load',
                'setData',
                'setIsOtherSessionsTerminated',
                'save',
                'getUserId',
                'getSessionId',
                'getUpdatedAt'
            ],
            [],
            '',
            false
        );

        $this->securityConfigMock = $this->getMockBuilder(\Magento\Security\Model\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->userMock = $this->getMock(
            '\Magento\User\Model\User',
            ['getId'],
            [],
            '',
            false
        );

        $this->dateTimeMock =  $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->remoteAddressMock =  $this->getMockBuilder(RemoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            '\Magento\Security\Model\AdminSessionsManager',
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
        $sessionId = 50;
        $timestamp = time();

        $olderThen = $timestamp - $sessionLifetime;

        $this->adminSessionInfoFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->currentSessionMock);

        $this->authSessionMock->expects($this->exactly(2))
            ->method('getSessionId')
            ->willReturn($sessionId);

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

        $this->currentSessionMock->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);

        $this->adminSessionInfoCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->adminSessionInfoCollectionMock);

        $this->adminSessionInfoCollectionMock->expects($this->once())->method('updateActiveSessionsStatus')
            ->with(
                \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_BY_LOGIN,
                $useId,
                $sessionId,
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
        $sessionId = 50;
        $lastUpdatedAt = '2015-12-31 23:59:59';
        $newUpdatedAt = '2016-01-01 00:00:30';

        $this->adminSessionInfoFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSessionMock);

        $this->authSessionMock->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);

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
    public function testProcessLogout()
    {
        $sessionId = 50;

        $this->adminSessionInfoFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSessionMock);

        $this->authSessionMock->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);

        $this->currentSessionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->currentSessionMock->expects($this->once())
            ->method('setData')
            ->with('status', \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT)
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
        $sessionId = 50;

        $this->adminSessionInfoFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSessionMock);

        $this->authSessionMock->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);

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
        $this->adminSessionInfoFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->currentSessionMock);
        $this->currentSessionMock->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue($sessionStatus));

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
                'sessionStatus' => \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_BY_LOGIN
            ],
            [
                'expectedResult' => __('Your current session is terminated by another user of this account.'),
                'sessionStatus' => \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_MANUALLY
            ],
            [
                'expectedResult' => __('Your current session has been expired.'),
                'sessionStatus' => \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT
            ],
            [
                'expectedResult' => __('Your account is temporarily disabled.'),
                'sessionStatus' => \Magento\Security\Model\AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            ],
            [
                'expectedResult' => '',
                'sessionStatus' => \Magento\Security\Model\AdminSessionInfo::LOGGED_IN
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
            ->with($useId, \Magento\Security\Model\AdminSessionInfo::LOGGED_IN)
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
        $sessionId = 50;
        $this->adminSessionInfoCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->adminSessionInfoCollectionMock);
        $this->authSessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock);
        $this->authSessionMock->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);
        $this->userMock->expects($this->once())
            ->method('getId')
            ->willReturn($useId);
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('filterByUser')
            ->with($useId, \Magento\Security\Model\AdminSessionInfo::LOGGED_IN, $sessionId)
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
            ->with($this->equalTo('status'), \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_MANUALLY)
            ->willReturnSelf();
        $this->adminSessionInfoCollectionMock->expects($this->once())
            ->method('save');

        $this->model->logoutOtherUserSessions();
    }
}
