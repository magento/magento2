<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Observer;

use Magento\AdminNotification\Model\Feed;
use Magento\AdminNotification\Model\FeedFactory;
use Magento\AdminNotification\Observer\PredispatchAdminActionControllerObserver;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\AdminNotification\Observer\PredispatchAdminActionControllerObserver
 */
class PredispatchAdminActionControllerObserverTest extends TestCase
{
    private const STATUS_ADMIN_LOGGED_IN = true;
    private const STATUS_ADMIN_IS_NOT_LOGGED = false;

    /**
     * @var Session|MockObject
     */
    private $backendAuthSessionMock;

    /**
     * @var Feed|MockObject
     */
    private $feedMock;

    /**
     * @var FeedFactory|MockObject
     */
    private $feedFactoryMock;

    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Testable Object
     *
     * @var PredispatchAdminActionControllerObserver
     */
    private $observer;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createMock(Observer::class);

        $this->backendAuthSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLoggedIn'])
            ->getMock();

        $this->feedMock = $this->getMockBuilder(Feed::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkUpdate'])
            ->getMock();

        $this->feedFactoryMock = $this->getMockBuilder(FeedFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->observer = $this->objectManager->getObject(
            PredispatchAdminActionControllerObserver::class,
            [
                '_feedFactory' => $this->feedFactoryMock,
                '_backendAuthSession' => $this->backendAuthSessionMock,
            ]
        );
    }

    /**
     * Test observer when admin user is logged in
     */
    public function testPredispatchObserverWhenAdminLoggedIn()
    {
        $this->backendAuthSessionMock
            ->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(self::STATUS_ADMIN_LOGGED_IN);

        $this->feedFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->feedMock);

        $this->feedMock
            ->expects($this->once())
            ->method('checkUpdate')
            ->willReturn($this->feedMock);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test observer when admin user is not logged in
     */
    public function testPredispatchObserverWhenAdminIsNotLoggedIn()
    {
        $this->backendAuthSessionMock
            ->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(self::STATUS_ADMIN_IS_NOT_LOGGED);

        $this->feedFactoryMock
            ->expects($this->never())
            ->method('create');

        $this->feedMock
            ->expects($this->never())
            ->method('checkUpdate');

        $this->observer->execute($this->observerMock);
    }
}
