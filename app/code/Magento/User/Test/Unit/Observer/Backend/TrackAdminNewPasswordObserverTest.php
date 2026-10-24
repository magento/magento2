<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Observer\Backend;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Model\User as UserModel;
use Magento\User\Observer\Backend\TrackAdminNewPasswordObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Magento\User\Observer\Backend\TrackAdminNewPasswordObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TrackAdminNewPasswordObserverTest extends TestCase
{
    use MockCreationTrait;

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
        $helper = new ObjectManager($this);

        $this->configInterfaceMock = $this->createMock(ConfigInterface::class);
        $this->userMock = $this->createMock(User::class);
        $this->authSessionMock = $this->createPartialMockWithReflection(
            Session::class,
            [
                'setPciAdminUserIsPasswordExpired',
                'unsPciAdminUserIsPasswordExpired',
                'getPciAdminUserIsPasswordExpired',
                'isLoggedIn',
                'clearStorage'
            ]
        );
        $this->managerInterfaceMock = $this->createMock(ManagerInterface::class);

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
        $eventObserverMock = $this->createPartialMock(Observer::class, ['getEvent']);

        /** @var Event|MockObject */
        $eventMock = $this->createPartialMockWithReflection(Event::class, ['getObject']);

        /** @var UserModel|MockObject $userMock */
        $userMock = $this->createPartialMock(UserModel::class, ['getId', 'getPassword', 'dataHasChangedFor']);

        $eventObserverMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getObject')->willReturn($userMock);
        $userMock->expects($this->once())->method('getId')->willReturn($uid);
        $userMock->expects($this->once())->method('getPassword')->willReturn($newPW);
        $userMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('password')
            ->willReturn(true);

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);
        $this->managerInterfaceMock
            ->expects($this->once())
            ->method('getMessages')
            ->willReturn($collectionMock);
        $this->authSessionMock->expects($this->once())->method('unsPciAdminUserIsPasswordExpired')->willReturn(null);

        $this->model->execute($eventObserverMock);
    }
}
