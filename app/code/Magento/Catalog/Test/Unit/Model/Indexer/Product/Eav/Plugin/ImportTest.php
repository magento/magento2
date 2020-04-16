<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $eavProcessorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eavProcessorMock->expects($this->once())
            ->method('markIndexerAsInvalid');

        $subjectMock = $this->getMockBuilder(Import::class)
            ->disableOriginalConstructor()
            ->getMock();
        $import = new \stdClass();

        $model = new \Magento\CatalogImportExport\Model\Indexer\Product\Eav\Plugin\Import($eavProcessorMock);

        $this->assertEquals(
            $import,
            $model->afterImportSource($subjectMock, $import)
        );
    }
}
