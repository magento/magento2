<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product;

use Magento\Catalog\Model\Indexer\Product\Full;
use Magento\Framework\Indexer\IndexerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Indexer\IndexerRegistry;

class FullTest extends TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var Full
     */
    private $full;
    
    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->indexerRegistryMock = $this->createMock(IndexerRegistry::class);

        $this->full = $this->objectManager->getObject(
            Full::class,
            [
                'indexerRegistry' => $this->indexerRegistryMock,
                'indexerList' => ['catalog_indexer', 'product_indexer', 'stock_indexer', 'search_indexer']
            ]
        );
    }

    public function testExecuteFull()
    {
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexAll');
        $this->indexerRegistryMock->expects($this->exactly(4))->method('get')->willReturn($indexerMock);

        $this->full->executeFull();
    }

    public function testExecuteList()
    {
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexList')->with([1, 2]);
        $this->indexerRegistryMock->expects($this->exactly(4))->method('get')->willReturn($indexerMock);

        $this->full->executeList([1, 2]);
    }

    public function testExecuteRow()
    {
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexRow')->with(1);
        $this->indexerRegistryMock->expects($this->exactly(4))->method('get')->willReturn($indexerMock);

        $this->full->executeRow(1);
    }
}
