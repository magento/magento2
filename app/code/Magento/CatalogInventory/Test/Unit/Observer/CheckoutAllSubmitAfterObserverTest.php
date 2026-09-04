<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Observer\CheckoutAllSubmitAfterObserver;
use Magento\CatalogInventory\Observer\ReindexQuoteInventoryObserver;
use Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class CheckoutAllSubmitAfterObserverTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var CheckoutAllSubmitAfterObserver
     */
    protected $observer;

    /**
     * @var SubtractQuoteInventoryObserver|MockObject
     */
    protected $subtractQuoteInventoryObserver;

    /**
     * @var ReindexQuoteInventoryObserver|MockObject
     */
    protected $reindexQuoteInventoryObserver;

    /**
     * @var Event|MockObject
     */
    protected $event;

    /**
     * @var Observer|MockObject
     */
    protected $eventObserver;

    protected function setUp(): void
    {
        $this->subtractQuoteInventoryObserver = $this->createMock(
            SubtractQuoteInventoryObserver::class
        );

        $this->reindexQuoteInventoryObserver = $this->createMock(
            ReindexQuoteInventoryObserver::class
        );

        $this->event = new Event();

        $this->eventObserver = $this->createMock(Observer::class);

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->observer = new CheckoutAllSubmitAfterObserver(
            $this->subtractQuoteInventoryObserver,
            $this->reindexQuoteInventoryObserver
        );
    }

    public function testCheckoutAllSubmitAfter()
    {
        $quote = $this->createPartialMockWithReflection(
            Quote::class,
            ['getAllVisibleItems', 'setAllVisibleItems', 'getAllItems', 'setAllItems']
        );

        $quote->setInventoryProcessed(false);
        $this->event->setQuote($quote);

        $this->subtractQuoteInventoryObserver->expects($this->once())
            ->method('execute')
            ->with($this->eventObserver);

        $this->reindexQuoteInventoryObserver->expects($this->once())
            ->method('execute')
            ->with($this->eventObserver);

        $this->observer->execute($this->eventObserver);
    }
}
