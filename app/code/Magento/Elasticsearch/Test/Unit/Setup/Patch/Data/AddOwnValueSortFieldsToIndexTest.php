<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Setup\Patch\Data;

use Magento\Catalog\Model\ResourceModel\Config as CatalogConfig;
use Magento\CatalogSearch\Model\Indexer\IndexerHandlerFactory;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\Model\Indexer\IndexerHandler;
use Magento\Elasticsearch\Setup\Patch\Data\AddOwnValueSortFieldsToIndex;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\StoreDimensionProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddOwnValueSortFieldsToIndexTest extends TestCase
{
    /**
     * @var IndexerHandlerFactory|MockObject
     */
    private $indexerHandlerFactory;

    /**
     * @var AddOwnValueSortFieldsToIndex
     */
    private $patch;

    protected function setUp(): void
    {
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->expects(self::once())->method('invalidate');
        $registry = $this->createMock(IndexerRegistry::class);
        $registry->method('get')->with('catalogsearch_fulltext')->willReturn($indexer);
        $config = $this->createMock(Config::class);
        $config->method('isElasticsearchEnabled')->willReturn(true);
        $dimensionProvider = $this->createMock(StoreDimensionProvider::class);
        $dimensionProvider->method('getIterator')->willReturnCallback(
            fn () => new \ArrayIterator([['scope' => 'store1'], ['scope' => 'store2']])
        );
        $catalogConfig = $this->createMock(CatalogConfig::class);
        $catalogConfig->method('getAttributesUsedForSortBy')->willReturn(
            [['attribute_code' => 'name'], ['attribute_code' => 'color']]
        );
        $this->indexerHandlerFactory = $this->createMock(IndexerHandlerFactory::class);

        $this->patch = new AddOwnValueSortFieldsToIndex(
            $registry,
            $config,
            $this->indexerHandlerFactory,
            $dimensionProvider,
            $catalogConfig
        );
    }

    public function testMappingIsUpdatedForEverySortableAttributeInEveryStore(): void
    {
        $handler = $this->createMock(IndexerHandler::class);
        $calls = [];
        $handler->expects(self::exactly(4))
            ->method('updateIndex')
            ->willReturnCallback(
                function (array $dimensions, string $attributeCode) use (&$calls, $handler) {
                    $calls[] = [$dimensions, $attributeCode];
                    return $handler;
                }
            );
        $this->indexerHandlerFactory->expects(self::once())
            ->method('create')
            ->with(['data' => ['indexer_id' => 'catalogsearch_fulltext']])
            ->willReturn($handler);

        $this->patch->apply();

        self::assertSame(
            [
                [['scope' => 'store1'], 'name'],
                [['scope' => 'store1'], 'color'],
                [['scope' => 'store2'], 'name'],
                [['scope' => 'store2'], 'color'],
            ],
            $calls
        );
    }

    public function testUnreachableSearchEngineFailsThePatchAfterInvalidatingTheIndex(): void
    {
        $this->indexerHandlerFactory->method('create')
            ->willThrowException(new \LogicException('Indexer handler is not available: opensearch'));

        $this->expectException(\LogicException::class);
        $this->patch->apply();
    }

    public function testMappingUpdateFailureFailsThePatch(): void
    {
        $handler = $this->createMock(IndexerHandler::class);
        $handler->method('updateIndex')->willThrowException(new \RuntimeException('No alive nodes'));
        $this->indexerHandlerFactory->method('create')->willReturn($handler);

        $this->expectException(\RuntimeException::class);
        $this->patch->apply();
    }
}
