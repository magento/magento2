<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\GoogleGtag\Helper\Data as GaDataHelper;
use Magento\GoogleGtag\Observer\SetGoogleAnalyticsOnOrderSuccessPageViewObserver;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetGoogleAnalyticsOnOrderSuccessPageViewObserverTest extends TestCase
{
    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @var SetGoogleAnalyticsOnOrderSuccessPageViewObserver
     */
    private $orderSuccessObserver;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->getMock();
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->orderSuccessObserver = $objectManager->getObject(
            SetGoogleAnalyticsOnOrderSuccessPageViewObserver::class,
            [
                'layout' => $this->layoutMock
            ]
        );
    }

    /**
     * Observer test
     */
    public function testExecuteWithNoOrderIds()
    {
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())
            ->method('__call')
            ->with(
                'getOrderIds'
            )
            ->willReturn([]);
        $this->layoutMock->expects($this->never())
            ->method('getBlock');

        $this->orderSuccessObserver->execute($this->observerMock);
    }

    /**
     * Observer test
     */
    public function testExecuteWithOrderIds()
    {
        $blockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderIds = [8];

        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())
            ->method('__call')
            ->with(
                'getOrderIds'
            )
            ->willReturn($orderIds);
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->willReturn($blockMock);
        $blockMock->expects($this->once())
            ->method('__call')
            ->with(
                'setOrderIds',
                [$orderIds]
            );

        $this->orderSuccessObserver->execute($this->observerMock);
    }
}
