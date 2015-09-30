<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Model\Backend\Observer;

use Magento\Framework\Exception\State\UserLockedException;

/**
 * Test class for Magento\User\Model\Backend\Observer\AuthObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Model\Backend\Config\ObserverConfig */
    protected $observerConfig;

    /** @var \Magento\Backend\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $configInterfaceMock;

    /** @var \Magento\User\Model\Resource\User|\PHPUnit_Framework_MockObject_MockObject */
    protected $userMock;

    /** @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlInterfaceMock;

    /** @var \Magento\Backend\Model\Auth\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $authSessionMock;

    /** @var \Magento\User\Model\UserFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $userFactoryMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptorMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var \Magento\Framework\Message\MessageInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageInterfaceMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    /** @var \Magento\User\Model\Backend\Observer\AuthObserver */
    protected $model;

    public function setUp()
    {
        $this->configInterfaceMock = $this->getMockBuilder('Magento\Backend\App\ConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->userMock = $this->getMockBuilder('Magento\User\Model\Resource\User')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->urlInterfaceMock = $this->getMockBuilder('Magento\Backend\Model\UrlInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->authSessionMock = $this->getMockBuilder('Magento\Backend\Model\Auth\Session')
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

        $this->userFactoryMock = $this->getMockBuilder('Magento\User\Model\UserFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->encryptorMock = $this->getMockBuilder('\Magento\Framework\Encryption\EncryptorInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->managerInterfaceMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->messageInterfaceMock = $this->getMockBuilder('Magento\Framework\Message\MessageInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->observerConfig = $helper->getObject(
            'Magento\User\Model\Backend\Config\ObserverConfig',
            [
                'backendConfig' => $this->configInterfaceMock
            ]
        );

        $this->model = $helper->getObject(
            'Magento\User\Model\Backend\Observer\AuthObserver',
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

        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'getUser', 'getResult'])
            ->getMock();

        /** @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getLockExpires', 'getPassword'])
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

        /** @var \Magento\Framework\Message\Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Framework\Message\Collection')
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
        $this->encryptorMock->expects($this->once())->method('isValidHashByVersion')->willReturn(true);
        $userMock->expects($this->once())->method('getPassword')->willReturn($userPassword);

        $this->model->adminAuthenticate($eventObserverMock);
    }

    public function testAdminAuthenticateThrowsException()
    {
        $password = "myP@sw0rd";
        $authResult = true;
        $lockExpires = '3015-07-08 11:14:15.638276';

        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'getUser', 'getResult'])
            ->getMock();

        /** @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getLockExpires', 'getPassword'])
            ->getMock();

        $eventObserverMock->expects($this->atLeastOnce())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getPassword')->willReturn($password);
        $eventMock->expects($this->once())->method('getUser')->willReturn($userMock);
        $eventMock->expects($this->once())->method('getResult')->willReturn($authResult);
        $userMock->expects($this->once())->method('getLockExpires')->willReturn($lockExpires);

        try {
            $this->model->adminAuthenticate($eventObserverMock);
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

        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getPassword', 'getUser', 'getResult'])
            ->getMock();

        /** @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder('Magento\User\Model\User')
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

        $this->model->adminAuthenticate($eventObserverMock);
    }
}
