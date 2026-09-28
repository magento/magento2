<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Plugin;

use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\ImportExport\Model\Import;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    public function testAfterImportSource()
    {
        $eavProcessorMock = $this->createMock(Processor::class);
        $eavProcessorMock->expects($this->once())
            ->method('markIndexerAsInvalid');

        $subjectMock = $this->createMock(Import::class);
        $import = new \stdClass();

        $model = new \Magento\CatalogImportExport\Model\Indexer\Product\Eav\Plugin\Import($eavProcessorMock);

        $this->assertEquals(
            $import,
            $model->afterImportSource($subjectMock, $import)
        );
    }
}
