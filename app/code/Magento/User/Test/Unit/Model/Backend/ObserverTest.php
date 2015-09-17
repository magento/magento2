<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Model\Backend\Observer;

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

    /** @var \Magento\User\Model\Backend\Observer|\PHPUnit_Framework_MockObject_MockObject */
    protected $model;

    public function setUp()
    {
        $this->authorizationInterfaceMock = $this->getMockBuilder('Magento\Framework\AuthorizationInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
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
            ->setMethods(['setPciAdminUserIsPasswordExpired'])
            ->getMock();

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
                'messageInterface' => $this->messageInterfaceMock
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
        $password_lifetime = 1;
        $isPasswordChangeForced = true;
        $message = __('It\'s time to change your password.');

        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $evenObserverMock */
        $evenObserverMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
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

        $evenObserverMock->expects($this->atLeastOnce())->method('getEvent')->willReturn($eventMock);
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
            ->willReturn($password_lifetime);

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
        $this->encryptorInterfaceMock->expects($this->once())->method('isValidHashByVersion')->willReturn(false);
        $userMock->expects($this->once())->method('getPassword')->willReturn($userPassword);

        /** @var \Magento\Framework\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject $abstractModelMock */
        $abstractModelMock = $this->getMockBuilder('Magento\Framework\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(['setForceNewPassword'])
            ->getMock();

        $this->userFactoryMock->expects($this->once())->method('create')->willReturn($abstractModelMock);
        $abstractModelMock->expects($this->once())->method('load')->willReturnSelf();
        $abstractModelMock->expects($this->once())->method('setNewPassword')->willReturnSelf();
        $abstractModelMock->expects($this->once())->method('setForceNewPassword')->willReturnSelf();
        $abstractModelMock->expects($this->once())->method('save')->willReturnSelf();

        $this->model->adminAuthenticate($evenObserverMock);
    }
}
