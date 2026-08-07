<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\MediaGalleryCleanup;
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
     * @var MediaGalleryProcessor
     */
    private $processor;

    /**
     * @var MediaGalleryCleanup|MockObject
     */
    private $mediaGalleryCleanup;

    protected function setUp(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $resourceConnection = $this->createMock(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($connection);

        $metadata = $this->createMock(EntityMetadata::class);
        $metadata->method('getLinkField')->willReturn('row_id');
        $metadataPool = $this->createMock(MetadataPool::class);
        $metadataPool->method('getMetadata')->willReturn($metadata);

        $resourceModel = $this->createMock(ResourceModel::class);
        $resourceFactory = $this->createMock(ResourceModelFactory::class);
        $resourceFactory->method('create')->willReturn($resourceModel);

        $this->mediaGalleryCleanup = $this->createMock(MediaGalleryCleanup::class);

        $this->processor = new MediaGalleryProcessor(
            $this->createMock(SkuProcessor::class),
            $metadataPool,
            $resourceConnection,
            $resourceFactory,
            $this->createMock(ProcessingErrorAggregatorInterface::class),
            $this->mediaGalleryCleanup
        );
    }

    public function testRemoveProductImagesDelegatesToCleanup(): void
    {
        $removals = [
            ['value_id' => 10, 'row_id' => 5, 'value' => '/o/l/old.jpg'],
        ];
        $this->mediaGalleryCleanup->expects($this->once())
            ->method('removeProductImages')
            ->with($removals, true);

        $this->processor->removeProductImages($removals, true);
    }

    public function testRemoveProductImagesDelegatesEmptyList(): void
    {
        $this->mediaGalleryCleanup->expects($this->once())
            ->method('removeProductImages')
            ->with([], false);

        $this->processor->removeProductImages([]);
    }
}
