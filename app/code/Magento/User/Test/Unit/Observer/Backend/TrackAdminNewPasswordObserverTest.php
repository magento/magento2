<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Observer\Backend;

/**
 * Test class for Magento\User\Observer\Backend\TrackAdminNewPasswordObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TrackAdminNewPasswordObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Model\Backend\Config\ObserverConfig */
    protected $observerConfig;

    /** @var \Magento\Backend\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $configInterfaceMock;

    /** @var \Magento\User\Model\ResourceModel\User|\PHPUnit_Framework_MockObject_MockObject */
    protected $userMock;

    /** @var \Magento\Backend\Model\Auth\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $authSessionMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var \Magento\User\Observer\Backend\TrackAdminNewPasswordObserver */
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

        $this->managerInterfaceMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->observerConfig = $helper->getObject(
            \Magento\User\Model\Backend\Config\ObserverConfig::class,
            [
                'backendConfig' => $this->configInterfaceMock
            ]
        );

        $this->model = $helper->getObject(
            \Magento\User\Observer\Backend\TrackAdminNewPasswordObserver::class,
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
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getObject'])
            ->getMock();

        /** @var \Magento\User\Model\User|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getPassword', 'getForceNewPassword'])
            ->getMock();

        $eventObserverMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getObject')->willReturn($userMock);
        $userMock->expects($this->once())->method('getId')->willReturn($uid);
        $userMock->expects($this->once())->method('getPassword')->willReturn($newPW);
        $userMock->expects($this->once())->method('getForceNewPassword')->willReturn(false);

        /** @var \Magento\Framework\Message\Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(\Magento\Framework\Message\Collection::class)
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
