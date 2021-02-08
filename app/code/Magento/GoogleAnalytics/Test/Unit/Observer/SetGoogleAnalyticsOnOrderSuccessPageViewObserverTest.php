<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleAnalytics\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\GoogleAnalytics\Helper\Data as GaDataHelper;
use Magento\GoogleAnalytics\Observer\SetGoogleAnalyticsOnOrderSuccessPageViewObserver;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class SetGoogleAnalyticsOnOrderSuccessPageViewObserverTest extends TestCase
{
    /**
     * @var Event|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventMock;

    /**
     * @var Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $observerMock;

    /**
     * @var GaDataHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $googleAnalyticsDataMock;

    /**
     * @var LayoutInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutMock;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var SetGoogleAnalyticsOnOrderSuccessPageViewObserver
     */
    private $orderSuccessObserver;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->googleAnalyticsDataMock = $this->getMockBuilder(GaDataHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->observerMock = $this->getMockBuilder(Observer::class)->getMock();
        $this->eventMock = $this->getMockBuilder(Event::class)->getMock();

        $objectManager = new ObjectManager($this);

        $this->orderSuccessObserver = $objectManager->getObject(
            SetGoogleAnalyticsOnOrderSuccessPageViewObserver::class,
            [
                'storeManager' => $this->storeManagerMock,
                'layout' => $this->layoutMock,
                'googleAnalyticsData' => $this->googleAnalyticsDataMock
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
                $this->equalTo('getOrderIds')
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
                $this->equalTo('getOrderIds')
            )
            ->willReturn($orderIds);
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->willReturn($blockMock);
        $blockMock->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('setOrderIds'),
                $this->equalTo([$orderIds])
            );

        $this->orderSuccessObserver->execute($this->observerMock);
    }
}
