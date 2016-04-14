<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Indexer\Stock\Plugin;

class ImportTest extends \PHPUnit_Framework_TestCase
{
    public function testAfterImportSource()
    {
        /**
         * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|
         *      \PHPUnit_Framework_MockObject_MockObject $processorMock
         */
        $processorMock = $this->getMock(
            'Magento\CatalogInventory\Model\Indexer\Stock\Processor',
            ['markIndexerAsInvalid', 'isIndexerScheduled'],
            [],
            '',
            false
        );

        $subjectMock = $this->getMock('Magento\ImportExport\Model\Import', [], [], '', false);
        $processorMock->expects($this->any())->method('markIndexerAsInvalid');
        $processorMock->expects($this->any())->method('isIndexerScheduled')->willReturn(false);

        $someData = [1, 2, 3];

        $model = new \Magento\CatalogImportExport\Model\Indexer\Stock\Plugin\Import($processorMock);
        $this->assertEquals($someData, $model->afterImportSource($subjectMock, $someData));
    }
}
