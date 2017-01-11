<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Observer\Backend;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Event\Observer;
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

/**
 * Test class for Magento\User\Observer\Backend\AuthObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObserverConfig */
    protected $observerConfig;

    /** @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $configInterfaceMock;

    /** @var User|\PHPUnit_Framework_MockObject_MockObject */
    protected $userMock;

    /** @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlInterfaceMock;

    /** @var Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $authSessionMock;

    /** @var UserFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $userFactoryMock;

    /** @var EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptorMock;

    /** @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageInterfaceMock;

    /** @var EventManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    /** @var AuthObserver */
    protected $model;

    protected function setUp()
    {
        $this->configInterfaceMock = $this->getMockBuilder(\Magento\Backend\App\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->userMock = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->urlInterfaceMock = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->authSessionMock = $this->getMockBuilder(\Magento\Backend\Model\Auth\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setPciAdminUserIsPasswordExpired',
                    'unsPciAdminUserIsPasswordExpired',
                    'getPciAdminUserIsPasswordExpired',
                    'isLoggedIn',
                    'clearStorage'
                ]
            )->getMock();

        $this->userFactoryMock = $this->getMockBuilder(\Magento\User\Model\UserFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->managerInterfaceMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->messageInterfaceMock = $this->getMockBuilder(\Magento\Framework\Message\MessageInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $helper = new ObjectManager($this);

        $this->observerConfig = $helper->getObject(
            \Magento\User\Model\Backend\Config\ObserverConfig::class,
            [
                'backendConfig' => $this->configInterfaceMock
            ]
        );

        $this->model = $helper->getObject(
            \Magento\User\Observer\Backend\AuthObserver::class,
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
        ];

        /** @var Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'getUser', 'getResult'])
            ->getMock();

        /** @var ModelUser|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getLockExpires', 'getPassword', 'save'])
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

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(\Magento\Framework\Message\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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

        /** @var Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'getUser', 'getResult'])
            ->getMock();

        /** @var ModelUser|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getLockExpires', 'getPassword'])
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

    public function testAdminAuthenticateUpdateLockingInfo()
    {
        $password = "myP@sw0rd";
        $uid = 123;
        $authResult = false;
        $firstFailure = '1965-07-08 11:14:15.638276';
        $numOfFailures = 5;

        /** @var Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'getUser', 'getResult'])
            ->getMock();

        /** @var ModelUser|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getFailuresNum', 'getFirstFailure'])
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
