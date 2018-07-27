<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

class ImportTest extends \PHPUnit_Framework_TestCase
{
    public function testAfterImportSource()
    {
        $processorMock = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Category\Product\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $processorMock->expects($this->once())
            ->method('markIndexerAsInvalid');

        $subjectMock = $this->getMockBuilder('Magento\ImportExport\Model\Import')
            ->disableOriginalConstructor()
            ->getMock();

        $import = true;

        $model = new \Magento\CatalogImportExport\Model\Indexer\Category\Product\Plugin\Import($processorMock);

        $this->assertEquals(
            $import,
            $model->afterImportSource($subjectMock, $import)
        );
    }
}
