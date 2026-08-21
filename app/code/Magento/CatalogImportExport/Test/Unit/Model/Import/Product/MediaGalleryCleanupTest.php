<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\Catalog\Model\Product\Image\RemoveDeletedImagesFromCache;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\CatalogImportExport\Model\Import\Product\MediaGalleryCleanup;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MediaGalleryCleanupTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var WriteInterface|MockObject
     */
    private $mediaDirectory;

    /**
     * @var RemoveDeletedImagesFromCache|MockObject
     */
    private $removeDeletedImagesFromCache;

    /**
     * @var MediaGalleryCleanup
     */
    private $cleanup;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(AdapterInterface::class);
        $resourceConnection = $this->createMock(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($this->connection);
        $resourceConnection->method('getTableName')->willReturnCallback(
            static function (string $table): string {
                return $table;
            }
        );

        $this->mediaDirectory = $this->createMock(WriteInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($this->mediaDirectory);

        $mediaConfig = $this->createMock(MediaConfig::class);
        $mediaConfig->method('getBaseMediaPath')->willReturn('catalog/product');

        $this->removeDeletedImagesFromCache = $this->createMock(RemoveDeletedImagesFromCache::class);

        $metadata = $this->createMock(EntityMetadata::class);
        $metadata->method('getLinkField')->willReturn('row_id');
        $metadataPool = $this->createMock(MetadataPool::class);
        $metadataPool->method('getMetadata')->willReturn($metadata);

        $this->cleanup = new MediaGalleryCleanup(
            $resourceConnection,
            $filesystem,
            $mediaConfig,
            $this->removeDeletedImagesFromCache,
            $this->createMock(LoggerInterface::class),
            $metadataPool
        );
    }

    public function testRemoveProductImagesWithEmptyListDoesNothing(): void
    {
        $this->connection->expects($this->never())->method('delete');
        $this->mediaDirectory->expects($this->never())->method('delete');
        $this->removeDeletedImagesFromCache->expects($this->never())->method('removeDeletedImagesFromCache');

        $this->cleanup->removeProductImages([]);
    }

    public function testRemoveProductImagesDeletesLinksValuesAndOrphanRow(): void
    {
        $this->connection->method('quoteInto')
            ->willReturnCallback(static function (string $text, $value): string {
                if (is_array($value)) {
                    return str_replace('?', implode(',', $value), $text);
                }
                return str_replace('?', (string)$value, $text);
            });

        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('distinct')->willReturnSelf();
        $select->method('group')->willReturnSelf();
        $this->connection->method('select')->willReturn($select);
        $this->connection->method('fetchCol')->willReturn([]);

        $deletedTables = [];
        $this->connection->expects($this->exactly(3))
            ->method('delete')
            ->willReturnCallback(function (string $table) use (&$deletedTables) {
                $deletedTables[] = $table;
                return 1;
            });

        $this->mediaDirectory->expects($this->never())->method('delete');
        $this->removeDeletedImagesFromCache->expects($this->never())->method('removeDeletedImagesFromCache');

        $this->cleanup->removeProductImages(
            [
                ['value_id' => 10, 'row_id' => 5, 'value' => '/o/l/old_extra.jpg'],
                ['value_id' => 10, 'row_id' => 5, 'value' => '/o/l/old_extra.jpg'],
            ],
            false
        );

        $this->assertSame(
            [
                'catalog_product_entity_media_gallery_value_to_entity',
                'catalog_product_entity_media_gallery_value',
                'catalog_product_entity_media_gallery',
            ],
            $deletedTables
        );
    }

    public function testRemoveProductImagesDeletesUnusedFileWhenEnabled(): void
    {
        $this->connection->method('quoteInto')
            ->willReturnCallback(static function (string $text, $value): string {
                if (is_array($value)) {
                    return str_replace('?', implode(',', $value), $text);
                }
                return str_replace('?', (string)$value, $text);
            });

        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('distinct')->willReturnSelf();
        $select->method('group')->willReturnSelf();
        $this->connection->method('select')->willReturn($select);
        $this->connection->method('fetchCol')->willReturn([]);
        // No remaining gallery rows for the path (batched COUNT via fetchPairs).
        $this->connection->method('fetchPairs')->willReturn([]);
        $this->connection->method('delete')->willReturn(1);

        $this->mediaDirectory->expects($this->once())
            ->method('isFile')
            ->with('catalog/product/o/l/old_extra.jpg')
            ->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->with('catalog/product/o/l/old_extra.jpg');
        $this->removeDeletedImagesFromCache->expects($this->once())
            ->method('removeDeletedImagesFromCache')
            ->with(['o/l/old_extra.jpg']);

        $this->cleanup->removeProductImages(
            [
                ['value_id' => 10, 'row_id' => 5, 'value' => '/o/l/old_extra.jpg'],
            ],
            true
        );
    }

    public function testRemoveProductImagesRejectsPathTraversalWhenDeletingFiles(): void
    {
        $this->connection->method('quoteInto')
            ->willReturnCallback(static function (string $text, $value): string {
                if (is_array($value)) {
                    return str_replace('?', implode(',', $value), $text);
                }
                return str_replace('?', (string)$value, $text);
            });

        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('distinct')->willReturnSelf();
        $select->method('group')->willReturnSelf();
        $this->connection->method('select')->willReturn($select);
        $this->connection->method('fetchCol')->willReturn([]);
        $this->connection->method('delete')->willReturn(1);

        $this->mediaDirectory->expects($this->never())->method('isFile');
        $this->mediaDirectory->expects($this->never())->method('delete');
        $this->removeDeletedImagesFromCache->expects($this->never())->method('removeDeletedImagesFromCache');

        $this->cleanup->removeProductImages(
            [
                ['value_id' => 10, 'row_id' => 5, 'value' => '/../../etc/passwd'],
            ],
            true
        );
    }

    public function testRemoveProductImagesBatchesUsageCountsForMultiplePaths(): void
    {
        $this->connection->method('quoteInto')
            ->willReturnCallback(static function (string $text, $value): string {
                if (is_array($value)) {
                    return str_replace('?', implode(',', $value), $text);
                }
                return str_replace('?', (string)$value, $text);
            });

        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('distinct')->willReturnSelf();
        $select->method('group')->willReturnSelf();
        $this->connection->method('select')->willReturn($select);
        $this->connection->method('fetchCol')->willReturn([]);
        $this->connection->method('delete')->willReturn(1);

        // One batched fetchPairs for usage counts (not per-path).
        $this->connection->expects($this->once())
            ->method('fetchPairs')
            ->willReturn([
                '/a/a/a.jpg' => '0',
                'a/a/a.jpg' => '0',
                '/b/b/b.jpg' => '2',
                'b/b/b.jpg' => '0',
            ]);

        $this->mediaDirectory->method('isFile')->willReturn(true);
        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->with('catalog/product/a/a/a.jpg');
        $this->removeDeletedImagesFromCache->expects($this->once())
            ->method('removeDeletedImagesFromCache')
            ->with(['a/a/a.jpg']);

        $this->cleanup->removeProductImages(
            [
                ['value_id' => 10, 'row_id' => 5, 'value' => '/a/a/a.jpg'],
                ['value_id' => 11, 'row_id' => 5, 'value' => '/b/b/b.jpg'],
            ],
            true
        );
    }

    public function testRemoveProductImagesKeepsSharedLinkedValueId(): void
    {
        $this->connection->method('quoteInto')
            ->willReturnCallback(static function (string $text, $value): string {
                if (is_array($value)) {
                    return str_replace('?', implode(',', $value), $text);
                }
                return str_replace('?', (string)$value, $text);
            });

        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('distinct')->willReturnSelf();
        $this->connection->method('select')->willReturn($select);
        $this->connection->method('fetchCol')->willReturn(['10']);

        $deletedTables = [];
        $this->connection->expects($this->exactly(2))
            ->method('delete')
            ->willReturnCallback(function (string $table) use (&$deletedTables) {
                $deletedTables[] = $table;
                return 1;
            });

        $this->mediaDirectory->expects($this->never())->method('delete');
        $this->removeDeletedImagesFromCache->expects($this->never())->method('removeDeletedImagesFromCache');

        $this->cleanup->removeProductImages(
            [
                ['value_id' => 10, 'row_id' => 5, 'value' => '/s/h/shared.jpg'],
            ],
            true
        );

        $this->assertSame(
            [
                'catalog_product_entity_media_gallery_value_to_entity',
                'catalog_product_entity_media_gallery_value',
            ],
            $deletedTables
        );
    }

    public function testRemoveProductImagesBatchDeletesMultiplePairs(): void
    {
        $this->connection->method('quoteInto')
            ->willReturnCallback(static function (string $text, $value): string {
                if (is_array($value)) {
                    return str_replace('?', implode(',', $value), $text);
                }
                return str_replace('?', (string)$value, $text);
            });

        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('distinct')->willReturnSelf();
        $select->method('group')->willReturnSelf();
        $this->connection->method('select')->willReturn($select);
        $this->connection->method('fetchCol')->willReturn([]);
        $this->connection->method('fetchPairs')->willReturn([]);

        $deleted = [];
        $this->connection->expects($this->exactly(3))
            ->method('delete')
            ->willReturnCallback(function (string $table, string $where = '') use (&$deleted) {
                $deleted[] = [$table, $where];
                return 1;
            });

        $this->cleanup->removeProductImages([
            ['value_id' => 10, 'row_id' => 5],
            ['value_id' => 11, 'row_id' => 6],
        ]);

        $this->assertSame('catalog_product_entity_media_gallery_value_to_entity', $deleted[0][0]);
        $this->assertSame('catalog_product_entity_media_gallery_value', $deleted[1][0]);
        $this->assertStringContainsString('(value_id = 10 AND row_id = 5)', $deleted[0][1]);
        $this->assertStringContainsString('(value_id = 11 AND row_id = 6)', $deleted[0][1]);
        $this->assertStringContainsString(' OR ', $deleted[0][1]);
        $this->assertSame($deleted[0][1], $deleted[1][1]);
    }
}
