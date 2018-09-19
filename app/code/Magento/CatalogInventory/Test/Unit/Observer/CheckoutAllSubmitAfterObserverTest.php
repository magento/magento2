<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Observer\CheckoutAllSubmitAfterObserver;

class CheckoutAllSubmitAfterObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CheckoutAllSubmitAfterObserver
     */
    protected $observer;

    /**
     * @var \Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subtractQuoteInventoryObserver;

    /**
     * @var \Magento\CatalogInventory\Observer\ReindexQuoteInventoryObserver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reindexQuoteInventoryObserver;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserver;

    protected function setUp()
    {
        $this->subtractQuoteInventoryObserver = $this->createMock(
            \Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver::class
        );

        $this->reindexQuoteInventoryObserver = $this->createMock(
            \Magento\CatalogInventory\Observer\ReindexQuoteInventoryObserver::class
        );

        $this->event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct', 'getCollection', 'getCreditmemo', 'getQuote', 'getWebsite'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));

        $this->observer = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\CatalogInventory\Observer\CheckoutAllSubmitAfterObserver::class,
            [
                'subtractQuoteInventoryObserver' => $this->subtractQuoteInventoryObserver,
                'reindexQuoteInventoryObserver' => $this->reindexQuoteInventoryObserver,
            ]
        );
    }

    public function testCheckoutAllSubmitAfter()
    {
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getInventoryProcessed']);
        $quote->expects($this->once())
            ->method('getInventoryProcessed')
            ->will($this->returnValue(false));

        $this->event->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($quote));

        $this->subtractQuoteInventoryObserver->expects($this->once())
            ->method('execute')
            ->with($this->eventObserver);

        $this->reindexQuoteInventoryObserver->expects($this->once())
            ->method('execute')
            ->with($this->eventObserver);

        $this->observer->execute($this->eventObserver);
    }
}
