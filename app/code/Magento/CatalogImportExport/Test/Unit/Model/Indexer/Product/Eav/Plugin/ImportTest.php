<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Indexer\Product\Eav\Plugin;

use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Catalog\Model\Product;
use Magento\CatalogImportExport\Model\Indexer\Product\Eav\Plugin\Import;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Import as ImportExportImport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    /**
     * @var Processor|MockObject
     */
    private $processorMock;

    /**
     * @var Import
     */
    private $model;

    /**
     * @var ImportExportImport|MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->processorMock = $this->createPartialMock(
            Processor::class,
            ['markIndexerAsInvalid', 'isIndexerScheduled']
        );

        $this->subjectMock = $this->createMock(ImportExportImport::class);

        $this->model = (new ObjectManager($this))->getObject(
            Import::class,
            [
                'indexerEavProcessor' => $this->processorMock
            ]
        );
    }

    public function testAfterImportSourceWithProductEntityAndIndexerScheduledDisabled()
    {
        $this->subjectMock->method('getEntity')->willReturn(Product::ENTITY);
        $this->processorMock->expects($this->once())->method('isIndexerScheduled')->willReturn(false);
        $this->processorMock->expects($this->once())->method('markIndexerAsInvalid');
        $someData = [1, 2, 3];
        $this->assertEquals($someData, $this->model->afterImportSource($this->subjectMock, $someData));
    }

    public function testAfterImportSourceWithProductEntityAndIndexerScheduledEnabled()
    {
        $this->subjectMock->method('getEntity')->willReturn(Product::ENTITY);
        $this->processorMock->expects($this->once())->method('isIndexerScheduled')->willReturn(true);
        $this->processorMock->expects($this->never())->method('markIndexerAsInvalid');
        $someData = [1, 2, 3];
        $this->assertEquals($someData, $this->model->afterImportSource($this->subjectMock, $someData));
    }

    public function testAfterImportSourceWithNonProductEntityDoesNotInvalidateIndexer()
    {
        $this->subjectMock->method('getEntity')->willReturn('customer');
        $this->processorMock->expects($this->never())->method('isIndexerScheduled');
        $this->processorMock->expects($this->never())->method('markIndexerAsInvalid');
        $someData = [1, 2, 3];
        $this->assertEquals($someData, $this->model->afterImportSource($this->subjectMock, $someData));
    }
}
