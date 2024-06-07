<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Observer\Backend;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Model\User as ModelUser;
use Magento\User\Model\UserFactory;
use Magento\User\Observer\Backend\AuthObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Magento\User\Observer\Backend\AuthObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthObserverTest extends TestCase
{
    /** @var ObserverConfig */
    protected $observerConfig;

    /** @var ConfigInterface|MockObject */
    protected $configInterfaceMock;

    /** @var User|MockObject */
    protected $userMock;

    /** @var UrlInterface|MockObject */
    protected $urlInterfaceMock;

    /** @var Session|MockObject */
    protected $authSessionMock;

    /** @var UserFactory|MockObject */
    protected $userFactoryMock;

    /** @var EncryptorInterface|MockObject */
    protected $encryptorMock;

    /** @var ManagerInterface|MockObject */
    protected $managerInterfaceMock;

    /** @var MessageInterface|MockObject */
    protected $messageInterfaceMock;

    /** @var EventManagerInterface|MockObject */
    protected $eventManagerMock;

    /** @var AuthObserver */
    protected $model;

    protected function setUp(): void
    {
        $this->configInterfaceMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->userMock = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['unlock', 'updateFailure', 'getLatestPassword'])
            ->getMock();

        $this->urlInterfaceMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'setPciAdminUserIsPasswordExpired',
                    'unsPciAdminUserIsPasswordExpired',
                    'getPciAdminUserIsPasswordExpired',
                ])
            ->onlyMethods(
                [
                    'isLoggedIn',
                    'clearStorage'
                ]
            )->getMock();

        $this->userFactoryMock = $this->getMockBuilder(UserFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->encryptorMock = $this->getMockBuilder(EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->managerInterfaceMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageInterfaceMock = $this->getMockBuilder(MessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $helper = new ObjectManager($this);

        $this->observerConfig = $helper->getObject(
            ObserverConfig::class,
            [
                'backendConfig' => $this->configInterfaceMock
            ]
        );

        $this->model = $helper->getObject(
            AuthObserver::class,
            [
                'observerConfig' => $this->observerConfig,
                'userResource' => $this->userMock,
                'url' => $this->urlInterfaceMock,
                'authSession' => $this->authSessionMock,
                'userFactory' => $this->userFactoryMock,
                'encryptor' => $this->encryptorMock,
                'messageManager' => $this->managerInterfaceMock,
                'messageInterface' => $this->messageInterfaceMock,
                'eventManager' => $this->eventManagerMock
            ]
        );
    }

    public function testAdminAuthenticate()
    {
        $password = "myP@sw0rd";
        $uid = 123;
        $authResult = true;
        $lockExpires = false;
        $userPassword = [
            'expires' => 1,
            'last_updated' => 1496248367
        ];

        /** @var Observer|MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])
            ->getMock();

        /** @var Event|MockObject */
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPassword', 'getUser', 'getResult'])
            ->getMock();

        /** @var ModelUser|MockObject $userMock */
        $userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->addMethods(['getLockExpires'])
            ->onlyMethods(['getId', 'getPassword', 'save'])
            ->getMock();

        $eventObserverMock->expects($this->atLeastOnce())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getPassword')->willReturn($password);
        $eventMock->expects($this->once())->method('getUser')->willReturn($userMock);
        $eventMock->expects($this->once())->method('getResult')->willReturn($authResult);
        $userMock->expects($this->atLeastOnce())->method('getId')->willReturn($uid);
        $userMock->expects($this->once())->method('getLockExpires')->willReturn($lockExpires);
        $this->userMock->expects($this->once())->method('unlock');
        $this->userMock->expects($this->once())->method('getLatestPassword')->willReturn($userPassword);
        $this->configInterfaceMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(1);

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLastAddedMessage'])
            ->getMock();

        $this->managerInterfaceMock->expects($this->once())->method('getMessages')->willReturn($collectionMock);
        $collectionMock
            ->expects($this->once())
            ->method('getLastAddedMessage')
            ->willReturn($this->messageInterfaceMock);
        $this->messageInterfaceMock->expects($this->once())->method('setIdentifier')->willReturnSelf();
        $this->authSessionMock->expects($this->once())->method('setPciAdminUserIsPasswordExpired');
        $this->encryptorMock->expects($this->once())->method('validateHashVersion')->willReturn(false);

        $this->model->execute($eventObserverMock);
    }

    public function testAdminAuthenticateThrowsException()
    {
        $password = "myP@sw0rd";
        $authResult = true;
        $lockExpires = '3015-07-08 11:14:15.638276';

        /** @var Observer|MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])
            ->getMock();

        /** @var Event|MockObject */
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPassword', 'getUser', 'getResult'])
            ->getMock();

        /** @var ModelUser|MockObject $userMock */
        $userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->addMethods(['getLockExpires'])
            ->onlyMethods(['getId', 'getPassword'])
            ->getMock();

        $eventObserverMock->expects($this->atLeastOnce())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getPassword')->willReturn($password);
        $eventMock->expects($this->once())->method('getUser')->willReturn($userMock);
        $eventMock->expects($this->once())->method('getResult')->willReturn($authResult);
        $userMock->expects($this->once())->method('getLockExpires')->willReturn($lockExpires);

        try {
            $this->model->execute($eventObserverMock);
        } catch (UserLockedException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    /**
     * @magentoConfigFixture admin/security/password_lifetime 1
     * @return void
     * @throws LocalizedException
     */
    public function testAdminAuthenticatePasswordExpire(): void
    {
        $password = "myP@sw0rd";
        $uid = 123;
        $authResult = true;
        $lockExpires = false;
        $userPassword = [
            'expires' => 1,
            'last_updated' => 1694661402
        ];

        /** @var Observer|MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])
            ->getMock();

        /** @var Event|MockObject */
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPassword', 'getUser', 'getResult'])
            ->getMock();

        /** @var ModelUser|MockObject $userMock */
        $userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->addMethods(['getLockExpires'])
            ->onlyMethods(['getId', 'getPassword', 'save'])
            ->getMock();

        $eventObserverMock->expects($this->atLeastOnce())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getPassword')->willReturn($password);
        $eventMock->expects($this->once())->method('getUser')->willReturn($userMock);
        $eventMock->expects($this->once())->method('getResult')->willReturn($authResult);
        $userMock->expects($this->atLeastOnce())->method('getId')->willReturn($uid);
        $userMock->expects($this->once())->method('getLockExpires')->willReturn($lockExpires);
        $this->userMock->expects($this->once())->method('unlock');
        $this->userMock->expects($this->once())->method('getLatestPassword')->willReturn($userPassword);
        $this->configInterfaceMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(1);

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLastAddedMessage'])
            ->getMock();

        $this->managerInterfaceMock->expects($this->once())->method('getMessages')->willReturn($collectionMock);
        $collectionMock
            ->expects($this->once())
            ->method('getLastAddedMessage')
            ->willReturn($this->messageInterfaceMock);
        $this->messageInterfaceMock->expects($this->once())->method('setIdentifier')->willReturnSelf();
        $this->authSessionMock->expects($this->once())->method('setPciAdminUserIsPasswordExpired');
        $this->encryptorMock->expects($this->once())->method('validateHashVersion')->willReturn(false);

        $this->model->execute($eventObserverMock);
    }

    public function testAdminAuthenticateUpdateLockingInfo()
    {
        $password = "myP@sw0rd";
        $uid = 123;
        $authResult = false;
        $firstFailure = '1965-07-08 11:14:15.638276';
        $numOfFailures = 5;

        /** @var Observer|MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])
            ->getMock();

        /** @var Event|MockObject */
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPassword', 'getUser', 'getResult'])
            ->getMock();

        /** @var ModelUser|MockObject $userMock */
        $userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->addMethods(['getFailuresNum', 'getFirstFailure'])
            ->getMock();

        $eventObserverMock->expects($this->atLeastOnce())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getPassword')->willReturn($password);
        $eventMock->expects($this->once())->method('getUser')->willReturn($userMock);
        $eventMock->expects($this->once())->method('getResult')->willReturn($authResult);
        $userMock->expects($this->once())->method('getId')->willReturn($uid);
        $this->configInterfaceMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(1);
        $userMock->expects($this->once())->method('getFailuresNum')->willReturn($numOfFailures);
        $userMock->expects($this->once())->method('getFirstFailure')->willReturn($firstFailure);
        $this->userMock->expects($this->once())->method('updateFailure');

        $this->model->execute($eventObserverMock);
    }
}
