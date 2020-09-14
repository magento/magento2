<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Indexer\Plugin;

use Magento\CatalogInventory\Model\Indexer\Stock\Processor as CatalogInventoryStockIndexer;
use Magento\CatalogSearch\Model\Indexer\Fulltext as CatalogSearchFulltextIndexer;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\Model\Indexer\Plugin\DependencyUpdaterPlugin;
use Magento\Framework\Indexer\Config\DependencyInfoProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Elasticsearch\Model\Indexer\Plugin\DependencyUpdaterPlugin class.
 */
class DependencyUpdaterPluginTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var DependencyUpdaterPlugin
     */
    private $plugin;

    /**
     * @var DependencyInfoProvider|MockObject
     */
    private $providerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock->expects($this->exactly(2))
            ->method('isElasticsearchEnabled')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->providerMock = $this->getMockBuilder(DependencyInfoProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new DependencyUpdaterPlugin($this->configMock);
    }

    /**
     * @return void
     */
    public function testAfterGetIndexerIdsToRunBefore(): void
    {
        $dependencies = [
            CatalogInventoryStockIndexer::INDEXER_ID,
        ];
        $indexerId = CatalogSearchFulltextIndexer::INDEXER_ID;

        $indexerIds = $this->plugin->afterGetIndexerIdsToRunBefore($this->providerMock, $dependencies, $indexerId);
        $this->assertContains(CatalogInventoryStockIndexer::INDEXER_ID, $indexerIds);

        $indexerIds = $this->plugin->afterGetIndexerIdsToRunBefore($this->providerMock, $dependencies, $indexerId);
        $this->assertNotContains(CatalogInventoryStockIndexer::INDEXER_ID, $indexerIds);
    }

    /**
     * @return void
     */
    public function testAfterGetIndexerIdsToRunAfter(): void
    {
        $dependencies = [
            CatalogSearchFulltextIndexer::INDEXER_ID,
        ];
        $indexerId = CatalogInventoryStockIndexer::INDEXER_ID;

        $indexerIds = $this->plugin->afterGetIndexerIdsToRunAfter($this->providerMock, $dependencies, $indexerId);
        $this->assertContains(CatalogSearchFulltextIndexer::INDEXER_ID, $indexerIds);

        $indexerIds = $this->plugin->afterGetIndexerIdsToRunAfter($this->providerMock, $dependencies, $indexerId);
        $this->assertNotContains(CatalogSearchFulltextIndexer::INDEXER_ID, $indexerIds);
    }
}
