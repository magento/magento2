<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Observer\Backend;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Observer\Backend\TrackAdminNewPasswordObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Magento\User\Observer\Backend\TrackAdminNewPasswordObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TrackAdminNewPasswordObserverTest extends TestCase
{
    /** @var ObserverConfig */
    protected $observerConfig;

    /** @var ConfigInterface|MockObject */
    protected $configInterfaceMock;

    /** @var User|MockObject */
    protected $userMock;

    /** @var Session|MockObject */
    protected $authSessionMock;

    /** @var ManagerInterface|MockObject */
    protected $managerInterfaceMock;

    /** @var TrackAdminNewPasswordObserver */
    protected $model;

    protected function setUp(): void
    {
        $this->configInterfaceMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->authSessionMock = $this->getMockBuilder(Session::class)
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

        $this->managerInterfaceMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $helper = new ObjectManager($this);

        $this->observerConfig = $helper->getObject(
            ObserverConfig::class,
            [
                'backendConfig' => $this->configInterfaceMock
            ]
        );

        $this->model = $helper->getObject(
            TrackAdminNewPasswordObserver::class,
            [
                'observerConfig' => $this->observerConfig,
                'userResource' => $this->userMock,
                'authSession' => $this->authSessionMock,
                'messageManager' => $this->managerInterfaceMock,
            ]
        );
    }

    public function testTrackAdminPassword()
    {
        $newPW = "mYn3wpassw0rd";
        $uid = 123;
        /** @var Observer|MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var Event|MockObject */
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getObject'])
            ->getMock();

        /** @var \Magento\User\Model\User|MockObject $userMock */
        $userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getPassword', 'getForceNewPassword'])
            ->getMock();

        $eventObserverMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getObject')->willReturn($userMock);
        $userMock->expects($this->once())->method('getId')->willReturn($uid);
        $userMock->expects($this->once())->method('getPassword')->willReturn($newPW);
        $userMock->expects($this->once())->method('getForceNewPassword')->willReturn(false);

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->managerInterfaceMock
            ->expects($this->once())
            ->method('getMessages')
            ->willReturn($collectionMock);
        $this->authSessionMock->expects($this->once())->method('unsPciAdminUserIsPasswordExpired')->willReturn(null);

        $this->model->execute($eventObserverMock);
    }
}
