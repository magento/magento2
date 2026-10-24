<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Integration\Model\Indexer;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceIndexProcessor;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor as FulltextIndexProcessor;
use PHPUnit\Framework\TestCase;

class SubscriptionsTest extends TestCase
{
    /**
     * @var PriceIndexProcessor
     */
    private PriceIndexProcessor $priceIndexProcessor;

    /**
     * @var FulltextIndexProcessor
     */
    private FulltextIndexProcessor $fulltextIndexProcessor;

    protected function setUp(): void
    {
        $this->priceIndexProcessor = Bootstrap::getObjectManager()->create(PriceIndexProcessor::class);
        $this->fulltextIndexProcessor = Bootstrap::getObjectManager()->create(FulltextIndexProcessor::class);
    }

    public function testSubscriptions(): void
    {
        $subscriptions = array_keys($this->priceIndexProcessor->getIndexer()->getView()->getSubscriptions());
        $this->assertNotContains('cataloginventory_stock_item', $subscriptions);
        $subscriptions = array_keys($this->fulltextIndexProcessor->getIndexer()->getView()->getSubscriptions());
        $this->assertNotContains('cataloginventory_stock_item', $subscriptions);
    }
}
