<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Observer\Backend;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
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

    /** @var \Magento\Backend\Model\Auth\Session|MockObject */
    protected $authSessionMock;

    /** @var ActionFlag|MockObject */
    protected $actionFlagMock;

    /** @var ManagerInterface|MockObject */
    protected $managerInterfaceMock;

    /** @var \Magento\Framework\Event\ManagerInterface|MockObject */
    protected $eventManagerMock;

    /** @var ForceAdminPasswordChangeObserver */
    protected $model;

    protected function setUp(): void
    {
        $this->authMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAllowed'])
            ->getMockForAbstractClass();

        $this->configInterfaceMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->urlInterfaceMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['clearStorage'])
            ->getMock();

        $this->authSessionMock = $this->getMockBuilder(\Magento\Backend\Model\Auth\Session::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'setPciAdminUserIsPasswordExpired',
                    'unsPciAdminUserIsPasswordExpired',
                    'getPciAdminUserIsPasswordExpired'
                ])
            ->onlyMethods(
                [
                    'isLoggedIn',
                    'clearStorage'
                ]
            )->getMock();

        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerInterfaceMock = $this->getMockBuilder(ManagerInterface::class)
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
        $eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])
            ->getMock();

        /** @var Event|MockObject */
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getControllerAction', 'getRequest'])
            ->getMock();

        $this->configInterfaceMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn(1);
        $this->authSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $eventObserverMock->expects($this->atLeastOnce())->method('getEvent')->willReturn($eventMock);
        /** @var Action $controllerMock */
        $controllerMock = $this->getMockBuilder(AbstractAction::class)
            ->disableOriginalConstructor()
            ->addMethods(['getRedirect'])
            ->onlyMethods(['getRequest'])
            ->getMockForAbstractClass();
        /** @var RequestInterface $requestMock */
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getFullActionName', 'setDispatched'])
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

        $this->model->execute($eventObserverMock);
    }
}
