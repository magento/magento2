<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Observer;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\Observer\CategoryProductIndexer;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CategoryProductIndexerTest
 */
class CategoryProductIndexerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CategoryProductIndexer
     */
    private $observer;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var Processor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $processorMock;

    /**
     * @var Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $observerMock;

    /**
     * Set Up method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->processorMock = $this->createMock(Processor::class);
        $this->observerMock = $this->createMock(Observer::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->observer = $objectManager->getObject(
            CategoryProductIndexer::class,
            [
                'config' => $this->configMock,
                'processor' => $this->processorMock,
            ]
        );
    }

    /**
     * Test if a category has changed products
     *
     * @return void
     */
    public function testExecuteIfCategoryHasChangedProducts()
    {
        $this->getProductIdsWithEnabledElasticSearch();
        $this->processorMock->expects($this->once())->method('isIndexerScheduled')->willReturn(true);
        $this->observer->execute($this->observerMock);
    }

    /**
     * Test if a category has changed products and not scheduled indexer
     *
     * @return void
     */
    public function testExecuteIfCategoryHasChangedProductsAndNotScheduledIndexer(): void
    {
        $this->getProductIdsWithEnabledElasticSearch();
        $this->processorMock->expects($this->once())->method('isIndexerScheduled')->willReturn(false);
        $this->processorMock->expects($this->never())->method('markIndexerAsInvalid');
        $this->observer->execute($this->observerMock);
    }

    /**
     * Test if a category has none changed products
     *
     * @return void
     */
    public function testExecuteIfCategoryHasNoneChangedProducts(): void
    {
        /** @var Event|\PHPUnit\Framework\MockObject\MockObject $eventMock */
        $eventMock = $this->createPartialMock(Event::class, ['getProductIds']);
        $this->configMock->expects($this->once())->method('isElasticsearchEnabled')->willReturn(true);

        $eventMock->expects($this->once())->method('getProductIds')->willReturn([]);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);

        $this->processorMock->expects($this->never())->method('isIndexerScheduled');
        $this->processorMock->expects($this->never())->method('markIndexerAsInvalid');

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test if ElasticSearch is disabled
     *
     * @return void
     */
    public function testExecuteIfElasticSearchIsDisabled(): void
    {
        /** @var Event|\PHPUnit\Framework\MockObject\MockObject $eventMock */
        $eventMock = $this->createPartialMock(Event::class, ['getProductIds']);
        $this->configMock->expects($this->once())->method('isElasticsearchEnabled')->willReturn(false);
        $eventMock->expects($this->never())->method('getProductIds')->willReturn([]);
        $this->observer->execute($this->observerMock);
    }

    /**
     * Get product ids with enabled ElasticSearch
     *
     * @return void
     */
    private function getProductIdsWithEnabledElasticSearch(): void
    {
        /** @var Event|\PHPUnit\Framework\MockObject\MockObject $eventMock */
        $eventMock = $this->createPartialMock(Event::class, ['getProductIds']);
        $this->configMock->expects($this->once())->method('isElasticsearchEnabled')->willReturn(true);
        $eventMock->expects($this->once())->method('getProductIds')->willReturn([1]);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
    }
}
