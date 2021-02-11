<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\Framework\Event;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockProcessor;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\CatalogInventory\Observer\ReindexQuoteInventoryObserver;
use Psr\Log\LoggerInterface;

/**
 * Test for \Magento\CatalogInventory\Observer\ReindexQuoteInventoryObserver.
 */
class ReindexQuoteInventoryObserverTest extends TestCase
{
    private const STUB_ID = 777;

    /**
     * @var ReindexQuoteInventoryObserver
     */
    private $model;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var StockProcessor|MockObject
     */
    private $stockProcessorMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var ItemsForReindex|MockObject
     */
    private $itemsForReindexMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->stockProcessorMock = $this->createMock(StockProcessor::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->itemsForReindexMock = $this->createMock(ItemsForReindex::class);

        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->observerMock = $this->createMock(Observer::class);
        $this->observerMock->method('getEvent')
            ->willReturn($eventMock);

        $this->model = $objectManager->getObject(
            ReindexQuoteInventoryObserver::class,
            [
                'stockIndexerProcessor' => $this->stockProcessorMock,
                'logger' => $this->loggerMock,
                'itemsForReindex' => $this->itemsForReindexMock,
            ]
        );
    }

    /**
     * Reindex quote while indexer unavailable
     *
     * @return void
     */
    public function testReindexQuoteWhileIndexerUnavailable(): void
    {
        $exception = new \Exception('Could not ping search engine');

        $quoteItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getProductId', 'getChildrenItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteItemMock->expects($this->exactly(2))
            ->method('getProductId')
            ->willReturn(self::STUB_ID);
        $quoteItemMock->expects($this->once())
            ->method('getChildrenItems')
            ->willReturn(null);
        $this->quoteMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$quoteItemMock]);
        $this->stockProcessorMock->expects($this->once())
            ->method('reindexList')
            ->with([self::STUB_ID => self::STUB_ID])
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('error');
        $this->itemsForReindexMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->itemsForReindexMock->expects($this->once())
            ->method('clear');

        $this->model->execute($this->observerMock);
    }
}
