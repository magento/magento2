<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Model\Backend\Observer;

/**
 * Test class for Magento\User\Model\Backend\Observer\PasswordObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PasswordObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $authMock;

    /** @var \Magento\User\Model\Backend\Config\ObserverConfig */
    protected $observerConfig;

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

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptorMock;

    /** @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject */
    protected $actionFlagMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var \Magento\Framework\Message\MessageInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageInterfaceMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    /** @var \Magento\User\Model\Backend\Observer\PasswordObserver */
    protected $model;

    public function setUp()
    {
        $this->authMock = $this->getMockBuilder('Magento\Framework\AuthorizationInterface')
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

        $this->encryptorMock = $this->getMockBuilder('\Magento\Framework\Encryption\EncryptorInterface')
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

        $this->observerConfig = $helper->getObject(
            'Magento\User\Model\Backend\Config\ObserverConfig',
            [
                'backendConfig' => $this->configInterfaceMock
            ]
        );

        $this->model = $helper->getObject(
            'Magento\User\Model\Backend\Observer\PasswordObserver',
            [
                'observerConfig' => $this->observerConfig,
                'authorization' => $this->authMock,
                'userResource' => $this->userMock,
                'url' => $this->urlInterfaceMock,
                'session' => $this->sessionMock,
                'authSession' => $this->authSessionMock,
                'actionFlag' => $this->actionFlagMock,
                'encryptor' => $this->encryptorMock,
                'messageManager' => $this->managerInterfaceMock,
                'messageInterface' => $this->messageInterfaceMock,
                'eventManager' => $this->eventManagerMock
            ]
        );
    }

    public function testCheckAdminPasswordChange()
    {
        $newPW = "mYn3wpassw0rd";
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
        $this->encryptorMock->expects($this->once())->method('isValidHash')->willReturn(false);
        $this->encryptorMock->expects($this->once())->method('getHash')->willReturn(md5($newPW));
        $this->userMock->method('getOldPasswords')->willReturn([md5('pw1'), md5('pw2')]);

        $this->model->checkAdminPasswordChange($eventObserverMock);
    }

    public function testCheckAdminPasswordChangeThrowsLocalizedExp()
    {
        $newPW = "mYn3wpassw0rd";
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
        $this->encryptorMock->expects($this->once())->method('isValidHash')->willReturn(true);
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
        $oldPW = "notsecure";
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
        $this->encryptorMock->expects($this->once())->method('getHash')->willReturn(md5($oldPW));

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
