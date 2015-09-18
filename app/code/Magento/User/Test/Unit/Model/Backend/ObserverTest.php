<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Test\Unit\Model\Backend\Observer;

use Magento\Framework\Exception\State\UserLockedException;

/**
 * Test class for Magento\User\Model\Backend\Observer
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $authorizationInterfaceMock;

    /** @var \Magento\Backend\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $configInterfaceMock;

    /** @var \Magento\User\Model\Resource\User|\PHPUnit_Framework_MockObject_MockObject */
    protected $userMock;

    /** @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlInterfaceMock;

    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Backend\Model\Auth\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $authSessionMock;

    /** @var \Magento\User\Model\UserFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $userFactoryMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptorInterfaceMock;

    /** @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject */
    protected $actionFlagMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var \Magento\Framework\Message\MessageInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageInterfaceMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    /** @var \Magento\User\Model\Backend\Observer|\PHPUnit_Framework_MockObject_MockObject */
    protected $model;

    public function setUp()
    {
        $this->authorizationInterfaceMock = $this->getMockBuilder('Magento\Framework\AuthorizationInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isAllowed'])
            ->getMock();

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

        $this->sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session')
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

        $this->encryptorInterfaceMock = $this->getMockBuilder('\Magento\Framework\Encryption\EncryptorInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->actionFlagMock = $this->getMockBuilder('Magento\Framework\App\ActionFlag')
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
        $this->model = $helper->getObject(
            'Magento\User\Model\Backend\Observer',
            [
                'authroization' => $this->authorizationInterfaceMock,
                'backendConfig' => $this->configInterfaceMock,
                'userResource' => $this->userMock,
                'url' => $this->urlInterfaceMock,
                'session' => $this->sessionMock,
                'authSession' => $this->authSessionMock,
                'userFactory' => $this->userFactoryMock,
                'actionFlag' => $this->actionFlagMock,
                'encryptor' => $this->encryptorInterfaceMock,
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
        $this->encryptorInterfaceMock->expects($this->once())->method('isValidHashByVersion')->willReturn(true);
        $userMock->expects($this->once())->method('getPassword')->willReturn($userPassword);

        $this->model->adminAuthenticate($eventObserverMock);
    }

    public function testAdminAuthenticateThrowsException()
    {
        $password = "myP@sw0rd";
        $uid = 123;
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
        $lockExpires = false;
        $userPassword = [
            'expires' => 1,
        ];
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

    public function testCheckAdminPasswordChange()
    {
        $newPW = "mYn3wpassw0rd";
        $oldPW = "unsecure";
        $uid = 123;
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getObject'])
            ->getMock();

        /** @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getNewPassword', 'getForceNewPassword'])
            ->getMock();

        $eventObserverMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getObject')->willReturn($userMock);
        $userMock->expects($this->atLeastOnce())->method('getNewPassword')->willReturn($newPW);
        $userMock->expects($this->once())->method('getForceNewPassword')->willReturn(false);
        $userMock->expects($this->once())->method('getId')->willReturn($uid);
        $this->encryptorInterfaceMock->expects($this->once())->method('isValidHash')->willReturn(false);
        $this->encryptorInterfaceMock->expects($this->once())->method('getHash')->willReturn(md5($newPW));
        $this->userMock->method('getOldPasswords')->willReturn([md5('pw1'), md5('pw2')]);

        $this->model->checkAdminPasswordChange($eventObserverMock);
    }

    public function testCheckAdminPasswordChangeThrowsLocalizedExp()
    {
        $newPW = "mYn3wpassw0rd";
        $oldPW = "unsecure";
        $uid = 123;
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getObject'])
            ->getMock();

        /** @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getNewPassword', 'getForceNewPassword'])
            ->getMock();

        $eventObserverMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getObject')->willReturn($userMock);
        $userMock->expects($this->atLeastOnce())->method('getNewPassword')->willReturn($newPW);
        $userMock->expects($this->once())->method('getForceNewPassword')->willReturn(false);
        $userMock->expects($this->once())->method('getId')->willReturn($uid);
        $this->encryptorInterfaceMock->expects($this->once())->method('isValidHash')->willReturn(true);
        $this->userMock->method('getOldPasswords')->willReturn([md5('pw1'), md5('pw2')]);

        try {
            $this->model->checkAdminPasswordChange($eventObserverMock);
        } catch (\Magento\Framework\Exception\LocalizedException $expected) {
            return;
        }
        $this->fail('An expected exception has not been raised.');
    }

    public function testTrackAdminPassword()
    {
        $newPW = "mYn3wpassw0rd";
        $oldPW = "unsecure";
        $uid = 123;
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getObject'])
            ->getMock();

        /** @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getNewPassword', 'getForceNewPassword'])
            ->getMock();

        $eventObserverMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getObject')->willReturn($userMock);
        $userMock->expects($this->once())->method('getId')->willReturn($uid);
        $userMock->expects($this->once())->method('getNewPassword')->willReturn($newPW);
        $this->configInterfaceMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(1);
        $userMock->expects($this->once())->method('getForceNewPassword')->willReturn(false);
        $this->encryptorInterfaceMock->expects($this->once())->method('getHash')->willReturn(md5($oldPW));

        /** @var \Magento\Framework\Message\Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder('Magento\Framework\Message\Collection')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->managerInterfaceMock
            ->expects($this->once())
            ->method('getMessages')
            ->willReturn($collectionMock);
        $this->authSessionMock->expects($this->once())->method('unsPciAdminUserIsPasswordExpired')->willReturn(null);

        $this->model->trackAdminNewPassword($eventObserverMock);
    }

    public function testForceAdminPasswordChange()
    {
        $url = ' ';
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getControllerAction', 'getRequest'])
            ->getMock();

        $this->configInterfaceMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(1);
        $this->authSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $eventObserverMock->expects($this->atLeastOnce())->method('getEvent')->willReturn($eventMock);
        /** @var \Magento\Framework\App\Action\Action $controllerMock */
        $controllerMock = $this->getMockBuilder('Magento\Framework\App\Action\AbstractAction')
            ->disableOriginalConstructor()
            ->setMethods(['getRedirect', 'getRequest'])
            ->getMockForAbstractClass();
        /** @var \Magento\Framework\App\RequestInterface $requestMock */
        $requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getFullActionName', 'setDispatched'])
            ->getMockForAbstractClass();
        $eventMock->expects($this->once())->method('getControllerAction')->willReturn($controllerMock);
        $eventMock->expects($this->once())->method('getRequest')->willReturn($requestMock);
        $this->authSessionMock->expects($this->once())->method('getPciAdminUserIsPasswordExpired')->willReturn(true);
        $requestMock->expects($this->once())->method('getFullActionName')->willReturn('not_in_array');

        $this->authSessionMock->expects($this->once())->method('clearStorage');
        $this->sessionMock->expects($this->once())->method('clearStorage');
        $this->managerInterfaceMock->expects($this->once())->method('addErrorMessage');
        $controllerMock->expects($this->once())->method('getRequest')->willReturn($requestMock);
        $requestMock->expects($this->once())->method('setDispatched')->willReturn(false);

        $this->model->forceAdminPasswordChange($eventObserverMock);
    }
}
