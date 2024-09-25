<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Observer\CheckoutAllSubmitAfterObserver;
use Magento\CatalogInventory\Observer\ReindexQuoteInventoryObserver;
use Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckoutAllSubmitAfterObserverTest extends TestCase
{
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

        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getProduct', 'getCollection', 'getCreditmemo', 'getQuote', 'getWebsite'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->observer = (new ObjectManager($this))->getObject(
            CheckoutAllSubmitAfterObserver::class,
            [
                'subtractQuoteInventoryObserver' => $this->subtractQuoteInventoryObserver,
                'reindexQuoteInventoryObserver' => $this->reindexQuoteInventoryObserver,
            ]
        );
    }

    public function testCheckoutAllSubmitAfter()
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->addMethods(['getInventoryProcessed'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->once())
            ->method('getInventoryProcessed')
            ->willReturn(false);

        $this->event->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $this->subtractQuoteInventoryObserver->expects($this->once())
            ->method('execute')
            ->with($this->eventObserver);

        $this->reindexQuoteInventoryObserver->expects($this->once())
            ->method('execute')
            ->with($this->eventObserver);

        $this->observer->execute($this->eventObserver);
    }
}
