<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\AdminSessionsManager testing
 */
class AdminSessionsManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Security\Model\AdminSessionsManager
     */
    protected $model;

    /**
     * @var \Magento\Security\Model\AdminSessionInfoFactory
     */
    protected $adminSessionInfoFactory;

    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     */
    protected $currentSession;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection
     */
    protected $collectionMock;

    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo
     */
    protected $adminSessionInfoResourceMock;

    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $securityConfig;

    /**
     * @var \Magento\User\Model\User
     */
    protected $userMock;

    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory
     */
    protected $adminSessionInfoCollectionFactory;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->adminSessionInfoFactory = $this->getMock(
            '\Magento\Security\Model\AdminSessionInfoFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->adminSessionInfoCollectionFactory = $this->getMock(
            '\Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->currentSession = $this->getMock(
            '\Magento\Security\Model\AdminSessionInfo',
            ['isActive', 'getStatus', 'load', 'setData', 'setIsOtherSessionsTerminated', 'save'],
            [],
            '',
            false
        );

        $this->authSession = $this->getMock(
            '\Magento\Backend\Model\Auth\Session',
            ['isActive', 'getStatus', 'getUser', 'getId', 'getSessionId', 'getUpdatedAt'],
            [],
            '',
            false
        );

        $this->collectionMock = $this->getMock(
            '\Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection',
            ['filterByUser', 'filterExpiredSessions', 'loadData', 'setDataToAll', 'save'],
            [],
            '',
            false
        );

        $this->adminSessionInfoResourceMock = $this->getMock(
            '\Magento\Security\Model\ResourceModel\AdminSessionInfo',
            ['updateStatusByUserId', 'deleteSessionsOlderThen'],
            [],
            '',
            false
        );

        $this->securityConfig = $this->getMock(
            '\Magento\Security\Helper\SecurityConfig',
            ['getAdminSessionLifetime', 'isAdminAccountSharingEnabled', 'getRemoteIp'],
            [],
            '',
            false
        );

        $this->userMock = $this->getMock(
            '\Magento\User\Model\User',
            ['getId'],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            '\Magento\Security\Model\AdminSessionsManager',
            [
                'authSession' => $this->authSession,
                'adminSessionInfoFactory' => $this->adminSessionInfoFactory,
                'adminSessionInfoResource' => $this->adminSessionInfoResourceMock,
                'adminSessionInfoCollectionFactory' => $this->adminSessionInfoCollectionFactory,
                'securityConfig' => $this->securityConfig
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

        $this->adminSessionInfoFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->currentSession);

        $this->authSession->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);

        $this->authSession->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock);
        $this->userMock->expects($this->once())
            ->method('getId')
            ->willReturn($useId);

        $this->securityConfig->expects($this->once())
            ->method('getRemoteIp')
            ->willReturn($ip);

        $this->currentSession->expects($this->once())
            ->method('setData')
            ->willReturnSelf();

        $this->currentSession->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->securityConfig->expects($this->once())
            ->method('getAdminSessionLifetime')
            ->willReturn($sessionLifetime);

        $this->securityConfig->expects($this->once())
            ->method('isAdminAccountSharingEnabled')
            ->willReturn(0);

        $this->adminSessionInfoResourceMock->expects($this->once())
            ->method('updateStatusByUserId')
            ->willReturn(1);

        $this->currentSession->expects($this->once())
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
        $updatedAt = '2015-12-31 23:59:59';

        $this->adminSessionInfoFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSession);

        $this->authSession->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);

        $this->currentSession->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->authSession->expects($this->once())
            ->method('getUpdatedAt')
            ->willReturn($updatedAt);


        $this->currentSession->expects($this->once())
            ->method('setData')
            ->with('updated_at', $updatedAt)
            ->willReturnSelf();

        $this->currentSession->expects($this->once())
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
        $updatedAt = '2015-12-31 23:59:59';

        $this->adminSessionInfoFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSession);

        $this->authSession->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);

        $this->currentSession->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->currentSession->expects($this->once())
            ->method('setData')
            ->with('status', \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT)
            ->willReturnSelf();

        $this->currentSession->expects($this->once())
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

        $this->adminSessionInfoFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSession);

        $this->authSession->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);

        $this->currentSession->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->assertEquals($this->currentSession, $this->model->getCurrentSession());
    }

    /**
     * @return void
     */
    public function testCleanExpiredSessions()
    {
        $this->adminSessionInfoResourceMock->expects($this->any())
            ->method('deleteSessionsOlderThen')
            ->with($this->securityConfig->getCurrentTimestamp() - \Magento\Security\Model\AdminSessionsManager::ADMIN_SESSION_LIFETIME)
            ->willReturnSelf();

        $this->model->cleanExpiredSessions();
    }

    /**
     * @param string $expectedResult
     * @param bool $isActiveSession
     * @param int $sessionStatus
     * @dataProvider dataProviderLogoutReasonMessage
     */
    public function testGetLogoutReasonMessage($expectedResult, $isActiveSession, $sessionStatus)
    {
        $this->adminSessionInfoFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->currentSession);
        $this->currentSession->expects($this->any())
            ->method('isActive')
            ->will($this->returnValue($isActiveSession));
        $this->currentSession->expects($this->any())
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
                'expectedResult' => 'Someone logged into this account from another device or browser.'
                    . ' Your current session is terminated.',
                'isActiveSession' => false,
                'sessionStatus' => \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_BY_LOGIN
            ],
            [
                'expectedResult' => 'Your current session is terminated by another user of this account.',
                'isActiveSession' => false,
                'sessionStatus' => \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_MANUALLY
            ],
            [
                'expectedResult' => 'Your current session has been expired.',
                'isActiveSession' => false,
                'sessionStatus' => \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT
            ],
            [
                'expectedResult' => '',
                'isActiveSession' => true,
                'sessionStatus' => ''
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGetSessionsForCurrentUser()
    {
        $useId = 1;
        $sessionLifetime = 100;
        $this->adminSessionInfoCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);
        $this->authSession->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock);
        $this->userMock->expects($this->once())
            ->method('getId')
            ->willReturn($useId);
        $this->collectionMock->expects($this->once())->method('filterByUser')
            ->with($useId, \Magento\Security\Model\AdminSessionInfo::LOGGED_IN)
            ->willReturnSelf();
        $this->securityConfig->expects($this->once())
            ->method('getAdminSessionLifetime')
            ->willReturn($sessionLifetime);
        $this->collectionMock->expects($this->once())
            ->method('filterExpiredSessions')
            ->with($sessionLifetime)
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('loadData')
            ->willReturnSelf();

        $this->assertSame($this->collectionMock, $this->model->getSessionsForCurrentUser());
    }

    /**
     * @return void
     */
    public function testLogoutAnotherUserSessions()
    {
        $useId = 1;
        $sessionLifetime = 100;
        $sessionId = 50;
        $this->adminSessionInfoCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);
        $this->authSession->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock);
        $this->authSession->expects($this->once())
            ->method('getSessionId')
            ->willReturn($sessionId);
        $this->userMock->expects($this->once())
            ->method('getId')
            ->willReturn($useId);
        $this->collectionMock->expects($this->once())
            ->method('filterByUser')
            ->with($useId, \Magento\Security\Model\AdminSessionInfo::LOGGED_IN, $sessionId)
            ->willReturnSelf();
        $this->securityConfig->expects($this->once())
            ->method('getAdminSessionLifetime')
            ->willReturn($sessionLifetime);
        $this->collectionMock->expects($this->once())
            ->method('filterExpiredSessions')
            ->with($sessionLifetime)
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('loadData')
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('setDataToAll')
            ->with($this->equalTo('status'), \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_MANUALLY)
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('save');

        $this->model->logoutAnotherUserSessions();
    }
}
