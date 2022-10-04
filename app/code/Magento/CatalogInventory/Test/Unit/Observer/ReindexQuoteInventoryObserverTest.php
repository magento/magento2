<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceProcessor;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockProcessor;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\CatalogInventory\Observer\ReindexQuoteInventoryObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for ReindexQuoteInventoryObserver
 */
class ReindexQuoteInventoryObserverTest extends TestCase
{
    /**
     * @var StockProcessor
     */
    private StockProcessor $stockIndexerProcessor;

    /**
     * @var PriceProcessor
     */
    private PriceProcessor $priceIndexer;

    /**
     * @var ItemsForReindex
     */
    private ItemsForReindex $itemsForReindex;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Observer
     */
    private Observer $observedObject;

    /**
     * @var Event
     */
    private Event $event;

    /**
     * @var Quote
     */
    private Quote $quote;

    /**
     * @var Item
     */
    private Item $quoteItem;

    /**
     * @var ReindexQuoteInventoryObserver
     */
    private ReindexQuoteInventoryObserver $sut;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->stockIndexerProcessor = $this->createMock(StockProcessor::class);
        $this->priceIndexer = $this->createMock(PriceProcessor::class);
        $this->itemsForReindex = $this->createMock(ItemsForReindex::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->observedObject = $this->createMock(Observer::class);
        $this->event = $this->createMock(Event::class);
        $this->quote = $this->createMock(Quote::class);
        $this->quoteItem = $this->createMock(Item::class);

        $this->sut = new ReindexQuoteInventoryObserver(
            $this->stockIndexerProcessor,
            $this->priceIndexer,
            $this->itemsForReindex,
            $this->logger
        );
    }

    /**
     * Test execute should re-index quote stock items.
     *
     * @test
     *
     * @return void
     */
    public function execute(): void
    {
        $this->observedObject->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->event->expects($this->once())
            ->method('getData')
            ->with('quote')
            ->willReturn($this->quote);

        $this->quote->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->quoteItem]);

        $this->quoteItem->expects($this->exactly(6))
            ->method('getData')
            ->withConsecutive(
                ['product_id'],
                ['product_id'],
                ['children_items'],
                ['product_id'],
                ['product_id'],
                ['product_id']
            )->willReturnOnConsecutiveCalls(1, 1, [$this->quoteItem], 1, 1, 1);

        $this->stockIndexerProcessor->expects($this->once())
            ->method('reindexList')
            ->with([1 => 1]);

        $this->itemsForReindex->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->quoteItem]);

        $this->priceIndexer->expects($this->once())
            ->method('reindexList')
            ->with([1]);

        $this->itemsForReindex->expects($this->once())
            ->method('clear');

        $this->sut->execute($this->observedObject);
    }

    /**
     * Test execute should log error on exception.
     *
     * @test
     *
     * @return void
     */
    public function executeShouldLogOnException(): void
    {
        $this->observedObject->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->event->expects($this->once())
            ->method('getData')
            ->with('quote')
            ->willReturn($this->quote);

        $this->quote->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->quoteItem]);

        $this->quoteItem->expects($this->exactly(3))
            ->method('getData')
            ->withConsecutive(
                ['product_id'],
                ['product_id'],
                ['children_items']
            )->willReturnOnConsecutiveCalls(1, 1, []);

        $this->stockIndexerProcessor->expects($this->once())
            ->method('reindexList')
            ->with([1 => 1])
            ->willThrowException(new LocalizedException(__('error')));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error while re-indexing order items: error');

        $this->stockIndexerProcessor->expects($this->once())
            ->method('markIndexerAsInvalid');

        $this->priceIndexer->expects($this->once())
            ->method('markIndexerAsInvalid');

        $this->sut->execute($this->observedObject);
    }
}
