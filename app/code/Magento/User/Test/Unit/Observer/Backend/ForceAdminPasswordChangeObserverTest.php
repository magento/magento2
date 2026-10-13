<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Observer\Backend;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\User\Observer\Backend\ForceAdminPasswordChangeObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Magento\User\Observer\Backend\ForceAdminPasswordChangeObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForceAdminPasswordChangeObserverTest extends TestCase
{
    use MockCreationTrait;

    /** @var AuthorizationInterface|MockObject */
    protected $authMock;

    /** @var ObserverConfig */
    protected $observerConfig;

    /** @var ConfigInterface|MockObject */
    protected $configInterfaceMock;

    /** @var UrlInterface|MockObject */
    protected $urlInterfaceMock;

    /** @var Session|MockObject */
    protected $sessionMock;

    /** @var AuthSession|MockObject */
    protected $authSessionMock;

    /** @var ActionFlag|MockObject */
    protected $actionFlagMock;

    /** @var ManagerInterface|MockObject */
    protected $managerInterfaceMock;

    /** @var EventManagerInterface|MockObject */
    protected $eventManagerMock;

    /** @var ForceAdminPasswordChangeObserver */
    protected $model;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->authMock = $this->createMock(AuthorizationInterface::class);
        $this->configInterfaceMock = $this->createMock(ConfigInterface::class);
        $this->urlInterfaceMock = $this->createMock(UrlInterface::class);
        $this->sessionMock = $this->createPartialMock(Session::class, ['clearStorage']);
        $this->authSessionMock = $this->createPartialMockWithReflection(
            AuthSession::class,
            [
                'setPciAdminUserIsPasswordExpired',
                'unsPciAdminUserIsPasswordExpired',
                'getPciAdminUserIsPasswordExpired',
                'isLoggedIn',
                'clearStorage'
            ]
        );
        $this->actionFlagMock = $this->createMock(ActionFlag::class);
        $this->managerInterfaceMock = $this->createMock(ManagerInterface::class);
        $this->eventManagerMock = $this->createMock(EventManagerInterface::class);

        $this->observerConfig = $helper->getObject(
            ObserverConfig::class,
            [
                'backendConfig' => $this->configInterfaceMock
            ]
        );

        $this->model = $helper->getObject(
            ForceAdminPasswordChangeObserver::class,
            [
                'observerConfig' => $this->observerConfig,
                'authorization' => $this->authMock,
                'url' => $this->urlInterfaceMock,
                'session' => $this->sessionMock,
                'authSession' => $this->authSessionMock,
                'actionFlag' => $this->actionFlagMock,
                'messageManager' => $this->managerInterfaceMock,
            ]
        );
    }

    public function testForceAdminPasswordChange()
    {
        /** @var Observer|MockObject $eventObserverMock */
        $eventObserverMock = $this->createPartialMock(Observer::class, ['getEvent']);

        /** @var Event|MockObject */
        $eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getControllerAction', 'getRequest']
        );

        $this->configInterfaceMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(1);
        $this->authSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $eventObserverMock->expects($this->atLeastOnce())->method('getEvent')->willReturn($eventMock);
        /** @var Action $controllerMock */
        $controllerMock = $this->createPartialMock(Action::class, ['getRequest', 'execute']);
        /** @var HttpRequest $requestMock */
        $requestMock = $this->createPartialMock(HttpRequest::class, ['getFullActionName', 'setDispatched']);
        $eventMock->expects($this->once())->method('getControllerAction')->willReturn($controllerMock);
        $eventMock->expects($this->once())->method('getRequest')->willReturn($requestMock);
        $this->authSessionMock->expects($this->once())->method('getPciAdminUserIsPasswordExpired')->willReturn(true);
        $requestMock->expects($this->once())->method('getFullActionName')->willReturn('not_in_array');

        $this->authSessionMock->expects($this->once())->method('clearStorage');
        $this->sessionMock->expects($this->once())->method('clearStorage');
        $this->managerInterfaceMock->expects($this->once())->method('addErrorMessage');
        $controllerMock->expects($this->once())->method('getRequest')->willReturn($requestMock);
        $requestMock->expects($this->once())->method('setDispatched')->willReturn(false);

        $this->model->execute($eventObserverMock);
    }
}
