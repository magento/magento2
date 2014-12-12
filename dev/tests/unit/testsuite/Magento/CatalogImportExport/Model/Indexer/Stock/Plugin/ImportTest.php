<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogImportExport\Model\Indexer\Stock\Plugin;

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
            ['markIndexerAsInvalid'],
            [],
            '',
            false
        );

        $subjectMock = $this->getMock('Magento\ImportExport\Model\Import', [], [], '', false);
        $processorMock->expects($this->once())->method('markIndexerAsInvalid');

        $someData = [1, 2, 3];

        $model = new \Magento\CatalogImportExport\Model\Indexer\Stock\Plugin\Import($processorMock);
        $this->assertEquals($someData, $model->afterImportSource($subjectMock, $someData));
    }
}
