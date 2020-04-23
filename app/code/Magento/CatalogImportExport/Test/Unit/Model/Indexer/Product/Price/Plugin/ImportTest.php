<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Indexer\Product\Price\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\CatalogImportExport\Model\Indexer\Product\Price\Plugin\Import;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\Indexer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImportTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Import
     */
    protected $_model;

    /**
     * @var Indexer|MockObject
     */
    protected $_indexerMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);

        $this->_indexerMock = $this->getMockBuilder(Indexer::class)
            ->addMethods(['getPriceIndexer'])
            ->onlyMethods(['getId', 'invalidate', 'isScheduled'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );

        $this->_model = $this->_objectManager->getObject(
            Import::class,
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
            ->with(Processor::INDEXER_ID)
            ->willReturn($this->_indexerMock);
        $this->_indexerMock->expects($this->any())
            ->method('isScheduled')
            ->willReturn(false);

        $importMock = $this->createMock(\Magento\ImportExport\Model\Import::class);
        $this->assertEquals('return_value', $this->_model->afterImportSource($importMock, 'return_value'));
    }
}
