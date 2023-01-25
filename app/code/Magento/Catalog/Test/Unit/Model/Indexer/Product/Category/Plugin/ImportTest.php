<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Category\Plugin;

use Magento\Catalog\Model\Indexer\Product\Category\Processor;
use Magento\ImportExport\Model\Import;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    public function testAfterImportSource()
    {
        $processorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $processorMock->expects($this->once())
            ->method('markIndexerAsInvalid');

        $subjectMock = $this->getMockBuilder(Import::class)
            ->disableOriginalConstructor()
            ->getMock();

        $import = true;

        $model = new \Magento\CatalogImportExport\Model\Indexer\Product\Category\Plugin\Import($processorMock);

        $this->assertEquals(
            $import,
            $model->afterImportSource($subjectMock, $import)
        );
    }
}
