<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Indexer\Product\Price\Plugin;

class ImportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\CatalogImportExport\Model\Indexer\Product\Price\Plugin\Import
     */
    protected $_model;

    /**
     * @var \Magento\Indexer\Model\Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_indexerMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_indexerMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer::class,
            ['getId', 'invalidate', 'getPriceIndexer', 'isScheduled']
        );
        $this->indexerRegistryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get']
        );

        $this->_model = $this->_objectManager->getObject(
            \Magento\CatalogImportExport\Model\Indexer\Product\Price\Plugin\Import::class,
            ['indexerRegistry' => $this->indexerRegistryMock]
        );
    }

    /**
     * Test AfterImportSource()
     */
    public function testAfterImportSource()
    {
        $this->_indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID)
            ->will($this->returnValue($this->_indexerMock));
        $this->_indexerMock->expects($this->any())
            ->method('isScheduled')
            ->will($this->returnValue(false));

        $importMock = $this->createMock(\Magento\ImportExport\Model\Import::class);
        $this->assertEquals('return_value', $this->_model->afterImportSource($importMock, 'return_value'));
    }
}
