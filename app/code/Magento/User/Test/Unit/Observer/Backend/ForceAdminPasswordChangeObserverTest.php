<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Observer\Backend;

/**
 * Test class for Magento\User\Observer\Backend\ForceAdminPasswordChangeObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForceAdminPasswordChangeObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $authMock;

    /** @var \Magento\User\Model\Backend\Config\ObserverConfig */
    protected $observerConfig;

    /** @var \Magento\Backend\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $configInterfaceMock;

    /** @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlInterfaceMock;

    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Backend\Model\Auth\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $authSessionMock;

    /** @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject */
    protected $actionFlagMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    /** @var \Magento\User\Observer\Backend\ForceAdminPasswordChangeObserver */
    protected $model;

    protected function setUp()
    {
        $this->authMock = $this->getMockBuilder(\Magento\Framework\AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAllowed'])
            ->getMock();

        $this->configInterfaceMock = $this->getMockBuilder(\Magento\Backend\App\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->urlInterfaceMock = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
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

        $this->actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->managerInterfaceMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->observerConfig = $helper->getObject(
            \Magento\User\Model\Backend\Config\ObserverConfig::class,
            [
                'backendConfig' => $this->configInterfaceMock
            ]
        );

        $this->model = $helper->getObject(
            \Magento\User\Observer\Backend\ForceAdminPasswordChangeObserver::class,
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
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
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
        $controllerMock = $this->getMockBuilder(\Magento\Framework\App\Action\AbstractAction::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRedirect', 'getRequest'])
            ->getMockForAbstractClass();
        /** @var \Magento\Framework\App\RequestInterface $requestMock */
        $requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
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

        $this->model->execute($eventObserverMock);
    }
}
