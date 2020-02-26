<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Config\Test\Unit\Observer\Config\Backend\Admin;

use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Observer\Config\Backend\Admin\AfterCustomUrlChangedObserver;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Config\Observer\Config\Backend\Admin\AfterCustomUrlChangedObserver
 */
class AfterCustomUrlChangedObserverTest extends TestCase
{
    /*
     * Stub backend start page URL
     */
    private const STUB_ADMIN_URL = 'https://localhost/admin/';

    /**
     * Testable Object
     *
     * @var AfterCustomUrlChangedObserver
     */
    private $observer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Data|MockObject
     */
    private $backendDataMock;

    /**
     * @var Registry|MockObject
     */
    private $coreRegistryMock;

    /**
     * @var Session|MockObject
     */
    private $authSessionMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createMock(Observer::class);

        $this->coreRegistryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();

        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['destroy'])
            ->getMock();

        $this->backendDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHomePageUrl'])
            ->getMock();

        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect', 'sendResponse'])
            ->getMockForAbstractClass();

        $this->observer = $this->objectManager->getObject(
            AfterCustomUrlChangedObserver::class,
            [
                'backendData' => $this->backendDataMock,
                'coreRegistry' => $this->coreRegistryMock,
                'authSession' => $this->authSessionMock,
                'response' => $this->responseMock,
            ]
        );
    }

    /**
     * Test for execute(), covers test case to log out user and redirect to new admin custom url
     */
    public function testExecuteLogOutUserWithRedirect(): void
    {
        $this->coreRegistryMock
            ->expects($this->once())
            ->method('registry')
            ->with('custom_admin_path_redirect')
            ->willReturn(true);

        $this->authSessionMock
            ->expects($this->once())
            ->method('destroy');

        $this->backendDataMock
            ->expects($this->once())
            ->method('getHomePageUrl')
            ->willReturn(self::STUB_ADMIN_URL);

        $this->responseMock
            ->expects($this->once())
            ->method('setRedirect')
            ->with(self::STUB_ADMIN_URL)
            ->willReturnSelf();

        $this->responseMock
            ->expects($this->once())
            ->method('sendResponse');

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test for execute(), covers test case to log out user and no redirect
     */
    public function testExecuteLogOutUserWithNoRedirect(): void
    {
        $this->coreRegistryMock
            ->expects($this->once())
            ->method('registry')
            ->with('custom_admin_path_redirect')
            ->willReturn(null);

        $this->authSessionMock
            ->expects($this->never())
            ->method('destroy');

        $this->backendDataMock
            ->expects($this->never())
            ->method('getHomePageUrl');

        $this->responseMock
            ->expects($this->never())
            ->method('setRedirect');

        $this->responseMock
            ->expects($this->never())
            ->method('sendResponse');

        $this->observer->execute($this->observerMock);
    }
}
