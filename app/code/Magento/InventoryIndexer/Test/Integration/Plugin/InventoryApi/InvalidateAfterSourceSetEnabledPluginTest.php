<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration\Plugin\InventoryApi;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests Indexer invalidation after Source enabled or disabled.
 */
class InvalidateAfterSourceSetEnabledPluginTest extends TestCase
{
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    protected function setUp()
    {
        $this->sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);
        $this->indexerRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);
    }

    /**
     * Tests Source enabling and disabling when no Stocks or Source Items are connected to current Source.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @dataProvider IndexerInvalidationOnlySourcesDataProvider
     * @param string $sourceCode
     * @param bool $enable
     * @param bool $expectedValid
     */
    public function testIndexerInvalidationOnlySources(string $sourceCode, bool $enable, bool $expectedValid)
    {
        $this->indexerInvalidationBase($sourceCode, $enable, $expectedValid);
    }

    /**
     * Tests Source enabling and disabling when no Stocks are connected to current Source.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     *
     * @dataProvider IndexerInvalidationNoStockLinksDataProvider
     * @param string $sourceCode
     * @param bool $enable
     * @param bool $expectedValid
     */
    public function testIndexerInvalidationNoStockLinks(string $sourceCode, bool $enable, bool $expectedValid)
    {
        $this->indexerInvalidationBase($sourceCode, $enable, $expectedValid);
    }

    /**
     * Tests Source enabling and disabling when no Source Items are connected to current Source.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @dataProvider IndexerInvalidationNoSourceItemsDataProvider
     * @param string $sourceCode
     * @param bool $enable
     * @param bool $expectedValid
     */
    public function testIndexerInvalidationNoSourceItems(string $sourceCode, bool $enable, bool $expectedValid)
    {
        $this->indexerInvalidationBase($sourceCode, $enable, $expectedValid);
    }

    /**
     * Tests Source enabling and disabling when both Stocks and Source Items are connected to current Source.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @dataProvider IndexerInvalidationFullDataProvider
     * @param string $sourceCode
     * @param bool $enable
     * @param bool $expectedValid
     */
    public function testIndexerInvalidationFull(string $sourceCode, bool $enable, bool $expectedValid)
    {
        $this->indexerInvalidationBase($sourceCode, $enable, $expectedValid);
    }

    /**
     * Contains generic logic for all Indexer invalidation after Source enabled or disabled tests.
     *
     * @param string $sourceCode
     * @param bool $enable
     * @param bool $expectedValid
     */
    private function indexerInvalidationBase(string $sourceCode, bool $enable, bool $expectedValid)
    {
        $indexer = $this->indexerRegistry->get(InventoryIndexer::INDEXER_ID);
        $indexer->reindexAll();

        $this->assertTrue($indexer->isValid());

        $source = $this->sourceRepository->get($sourceCode);
        $source->setEnabled($enable);
        $this->sourceRepository->save($source);
        $actualValid = $indexer->isValid();

        $this->assertEquals(
            $expectedValid,
            $actualValid
        );
    }

    /**
     * Data provider for testIndexerInvalidationOnlySources.
     *
     * @return array
     */
    public function indexerInvalidationOnlySourcesDataProvider()
    {
        return [
            ['eu-1', true, true],
            ['eu-1', false, true],
            ['eu-disabled', true, true],
            ['eu-disabled', false, true],
        ];
    }

    /**
     * Data provider for testIndexerInvalidationNoStockLinks.
     *
     * @return array
     */
    public function indexerInvalidationNoStockLinksDataProvider()
    {
        return [
            ['eu-1', true, true],
            ['eu-1', false, true],
            ['eu-disabled', true, true],
            ['eu-disabled', false, true],
        ];
    }

    /**
     * Data provider for testIndexerInvalidationNoSourceItems.
     *
     * @return array
     */
    public function indexerInvalidationNoSourceItemsDataProvider()
    {
        return [
            ['eu-1', true, true],
            ['eu-1', false, true],
            ['eu-disabled', true, true],
            ['eu-disabled', false, true],
        ];
    }

    /**
     * Data provider for testIndexerInvalidationFull.
     *
     * @return array
     */
    public function indexerInvalidationFullDataProvider()
    {
        return [
            ['eu-1', true, true],
            ['eu-1', false, false],
            ['eu-disabled', true, false],
            ['eu-disabled', false, true],
        ];
    }
}
