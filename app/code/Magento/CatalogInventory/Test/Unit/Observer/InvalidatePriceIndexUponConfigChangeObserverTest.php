<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Observer\InvalidatePriceIndexUponConfigChangeObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Testing invalidating product price index onn config changing
 */
class InvalidatePriceIndexUponConfigChangeObserverTest extends TestCase
{
    /**
     * @var InvalidatePriceIndexUponConfigChangeObserver
     */
    private $observer;

    /**
     * @var Processor|MockObject
     */
    private $priceIndexProcessorMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var IndexerInterface|MockObject
     */
    private $indexerMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->priceIndexProcessorMock = $this->createMock(Processor::class);
        $this->indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getChangedPaths'])
            ->getMock();

        $this->observer = $objectManager->getObject(
            InvalidatePriceIndexUponConfigChangeObserver::class,
            [
                'priceIndexProcessor' => $this->priceIndexProcessorMock
            ]
        );
    }

    /**
     * Testing invalidating product price index on catalog inventory config changes
     */
    public function testInvalidatingPriceOnChangingOutOfStockConfig()
    {
        $changedPaths = [Configuration::XML_PATH_SHOW_OUT_OF_STOCK];

        $this->eventMock->expects($this->once())
            ->method('getChangedPaths')
            ->willReturn($changedPaths);
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->indexerMock->expects($this->once())
            ->method('invalidate');
        $this->priceIndexProcessorMock->expects($this->once())
            ->method('getIndexer')
            ->willReturn($this->indexerMock);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Testing invalidating product price index on changing any other config
     */
    public function testInvalidatingPriceOnChangingAnyOtherConfig()
    {
        $changedPaths = [Configuration::XML_PATH_ITEM_AUTO_RETURN];

        $this->eventMock->expects($this->once())
            ->method('getChangedPaths')
            ->willReturn($changedPaths);
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->indexerMock->expects($this->never())
            ->method('invalidate');
        $this->priceIndexProcessorMock->expects($this->never())
            ->method('getIndexer')
            ->willReturn($this->indexerMock);

        $this->observer->execute($this->observerMock);
    }
}
