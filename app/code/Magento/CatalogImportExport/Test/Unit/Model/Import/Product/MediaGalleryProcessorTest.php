<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\MediaGalleryProcessor;
use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaGalleryProcessorTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var MediaGalleryProcessor
     */
    private $processor;

    /**
     * @var ResourceModel|MockObject
     */
    private $resourceModel;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(AdapterInterface::class);
        $resourceConnection = $this->createMock(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($this->connection);

        $metadata = $this->createMock(EntityMetadata::class);
        $metadata->method('getLinkField')->willReturn('row_id');
        $metadataPool = $this->createMock(MetadataPool::class);
        $metadataPool->method('getMetadata')->willReturn($metadata);

        $this->resourceModel = $this->createMock(ResourceModel::class);
        $this->resourceModel->method('getTable')->willReturnCallback(
            static function (string $table): string {
                return $table;
            }
        );
        $resourceFactory = $this->createMock(ResourceModelFactory::class);
        $resourceFactory->method('create')->willReturn($this->resourceModel);

        $this->processor = new MediaGalleryProcessor(
            $this->createMock(SkuProcessor::class),
            $metadataPool,
            $resourceConnection,
            $resourceFactory,
            $this->createMock(ProcessingErrorAggregatorInterface::class)
        );
    }

    public function testRemoveProductImagesDeletesLinksAndValuesInBatch(): void
    {
        $this->connection->method('quoteInto')
            ->willReturnCallback(static function (string $text, $value): string {
                return str_replace('?', (string)$value, $text);
            });

        $deleted = [];
        $this->connection->expects($this->exactly(2))
            ->method('delete')
            ->willReturnCallback(function (string $table, string $where) use (&$deleted) {
                $deleted[] = [$table, $where];
                return 1;
            });

        $this->processor->removeProductImages([
            ['value_id' => 10, 'row_id' => 5],
            ['value_id' => 10, 'row_id' => 5],
            ['value_id' => 11, 'row_id' => 6],
        ]);

        $this->assertCount(2, $deleted);
        $this->assertSame('catalog_product_entity_media_gallery_value_to_entity', $deleted[0][0]);
        $this->assertSame('catalog_product_entity_media_gallery_value', $deleted[1][0]);
        $this->assertStringContainsString('(value_id = 10 AND row_id = 5)', $deleted[0][1]);
        $this->assertStringContainsString('(value_id = 11 AND row_id = 6)', $deleted[0][1]);
        $this->assertStringContainsString(' OR ', $deleted[0][1]);
        $this->assertSame($deleted[0][1], $deleted[1][1]);
    }

    public function testRemoveProductImagesWithEmptyListDoesNothing(): void
    {
        $this->connection->expects($this->never())->method('delete');
        $this->processor->removeProductImages([]);
    }
}
