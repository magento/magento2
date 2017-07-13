<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Indexer\Product\Flat\Plugin;

class ImportTest extends \PHPUnit\Framework\TestCase
{
    public function testAfterImportSource()
    {
        /**
         * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|
         *      \PHPUnit_Framework_MockObject_MockObject $processorMock
         */
        $processorMock = $this->createPartialMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\Processor::class,
            ['markIndexerAsInvalid']
        );

        $subjectMock = $this->createMock(\Magento\ImportExport\Model\Import::class);
        $processorMock->expects($this->once())->method('markIndexerAsInvalid');

        $someData = [1, 2, 3];

        $model = new \Magento\CatalogImportExport\Model\Indexer\Product\Flat\Plugin\Import($processorMock);
        $this->assertEquals($someData, $model->afterImportSource($subjectMock, $someData));
    }
}
