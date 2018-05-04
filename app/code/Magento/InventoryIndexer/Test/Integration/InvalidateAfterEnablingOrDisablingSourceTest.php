<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests Indexer invalidation after Source enabled or disabled.
 */
class InvalidateAfterEnablingOrDisablingSourceTest extends TestCase
{
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    protected function setUp()
    {
        $this->sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);
        /** @var IndexerRegistry $indexerRegistry */
        $indexerRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);
        $this->indexer = $indexerRegistry->get(InventoryIndexer::INDEXER_ID);
    }

    /**
     * Tests Source enabling and disabling when both Stocks and Source Items are connected to current Source.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @dataProvider indexerInvalidationDataProvider
     * @param string $sourceCode
     * @param bool $enable
     * @param bool $expectedValid
     *
     * @magentoDbIsolation disabled
     */
    public function testIndexerInvalidation(string $sourceCode, bool $enable, bool $expectedValid)
    {
        $this->setSourceEnabledStatus($sourceCode, $enable);

        $this->assertEquals($expectedValid, $this->indexer->isValid());
    }

    /**
     * @return array
     */
    public function indexerInvalidationDataProvider(): array
    {
        return [
            ['eu-1', true, true],
            ['eu-1', false, false],
            ['eu-disabled', true, false],
            ['eu-disabled', false, true],
        ];
    }

    /**
     * Tests Source enabling and disabling when no Stocks or Source Items are connected to Source.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @dataProvider sourceDoesNotHaveAllRelationsDataProvider
     * @param string $sourceCode
     * @param bool $enable
     * @param bool $expectedValid
     */
    public function testIndexerInvalidationIfSourceDoesNotHaveAnyRelations(
        string $sourceCode,
        bool $enable,
        bool $expectedValid
    ) {
        $this->setSourceEnabledStatus($sourceCode, $enable);

        $this->assertEquals($expectedValid, $this->indexer->isValid());
    }

    /**
     * Tests Source enabling and disabling when no Stocks are connected to current Source.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @dataProvider sourceDoesNotHaveAllRelationsDataProvider
     * @param string $sourceCode
     * @param bool $enable
     * @param bool $expectedValid
     */
    public function testIndexerInvalidationIfSourceDoesNotHaveStockLinks(
        string $sourceCode,
        bool $enable,
        bool $expectedValid
    ) {
        $this->setSourceEnabledStatus($sourceCode, $enable);

        $this->assertEquals($expectedValid, $this->indexer->isValid());
    }

    /**
     * Tests Source enabling and disabling when no Source Items are connected to current Source.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @dataProvider sourceDoesNotHaveAllRelationsDataProvider
     * @param string $sourceCode
     * @param bool $enable
     * @param bool $expectedValid
     *
     * @magentoDbIsolation disabled
     */
    public function testIndexerInvalidationIfSourceDoesNotHaveSourceItems(
        string $sourceCode,
        bool $enable,
        bool $expectedValid
    ) {
        $this->setSourceEnabledStatus($sourceCode, $enable);

        $this->assertEquals($expectedValid, $this->indexer->isValid());
    }

    /**
     * @return array
     */
    public function sourceDoesNotHaveAllRelationsDataProvider(): array
    {
        return [
            ['eu-1', true, true],
            ['eu-1', false, true],
            ['eu-disabled', true, true],
            ['eu-disabled', false, true],
        ];
    }

    /**
     * @param string $sourceCode
     * @param bool $enable
     * @return void
     */
    private function setSourceEnabledStatus(string $sourceCode, bool $enable)
    {
        $source = $this->sourceRepository->get($sourceCode);
        $source->setEnabled($enable);
        $this->sourceRepository->save($source);
    }
}
