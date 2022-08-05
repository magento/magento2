<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Reports\Model\Event as ReportsEventModel;
use Magento\Reports\Model\ReportStatus;
use Magento\Reports\Observer\CheckoutCartAddProductObserver;
use Magento\Reports\Observer\EventSaver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for \Magento\Reports\Observer\CheckoutCartAddProductObserver
 */
class CheckoutCartAddProductObserverTest extends TestCase
{
    const STUB_QUOTE_PARENT_ITEM_ID = 1;
    const STUB_QUOTE_ITEM_ID = 2;

    /**
     * @var MockObject|EventSaver
     */
    private $eventSaverMock;

    /**
     * @var MockObject|ReportStatus
     */
    private $reportStatusMock;

    /**
     * @var MockObject|Observer
     */
    private $eventObserverMock;

    /**
     * @var MockObject|Event
     */
    private $eventMock;

    /**
     * @var MockObject|QuoteItem
     */
    private $quoteItemMock;

    /**
     * @var CheckoutCartAddProductObserver
     */
    private $observer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->eventSaverMock = $this->createMock(EventSaver::class);
        $this->reportStatusMock = $this->createMock(ReportStatus::class);
        $this->eventObserverMock = $this->createMock(Observer::class);
        $this->quoteItemMock = $this->createMock(QuoteItem::class);
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItem'])
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->observer = $objectManager->getObject(
            CheckoutCartAddProductObserver::class,
            [
                'eventSaver' => $this->eventSaverMock,
                'reportStatus' => $this->reportStatusMock
            ]
        );
    }

    /**
     * The case when event has to be successfully saved
     */
    public function testExecuteExpectsSaveCalledWhenNewProductAdded()
    {
        $this->configureMocksWhenReportsEnabled();
        $this->quoteItemMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->quoteItemMock->expects($this->once())
            ->method('getParentItem')
            ->willReturn(null);

        $this->eventSaverMock->expects($this->once())->method('save');
        $this->observer->execute($this->eventObserverMock);
    }

    /**
     * Test the method when 'Product Added To Cart' Report is disabled in configuration
     */
    public function testExecuteWhenReportsDisabled()
    {
        $this->reportStatusMock->expects($this->once())
            ->method('isReportEnabled')
            ->with(ReportsEventModel::EVENT_PRODUCT_TO_CART)
            ->willReturn(false);

        $this->checkOriginalMethodIsNeverExecuted();
    }

    /**
     * Test when Quote Item has Id
     */
    public function testExecuteWithQuoteItemIdSet()
    {
        $this->configureMocksWhenReportsEnabled();
        $this->quoteItemMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::STUB_QUOTE_ITEM_ID);

        $this->checkOriginalMethodIsNeverExecuted();
    }

    /**
     * Test when Quote Item has Parent Item set
     */
    public function testExecuteWithQuoteParentItemIdSet()
    {
        $this->configureMocksWhenReportsEnabled();
        $this->quoteItemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn(self::STUB_QUOTE_PARENT_ITEM_ID);

        $this->checkOriginalMethodIsNeverExecuted();
    }

    /**
     * Common mocks assertions when Report is enabled in configuration
     */
    private function configureMocksWhenReportsEnabled()
    {
        $this->reportStatusMock->expects($this->once())
            ->method('isReportEnabled')
            ->willReturn(true);
        $this->eventObserverMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())
            ->method('getItem')
            ->willReturn($this->quoteItemMock);
    }

    /**
     * Checking that the method will be never executed
     */
    private function checkOriginalMethodIsNeverExecuted()
    {
        $this->eventSaverMock->expects($this->never())->method('save');
        $this->observer->execute($this->eventObserverMock);
    }
}
