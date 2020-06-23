<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product;

use Magento\Catalog\Model\Indexer\Product\Full;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FullTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var Full
     */
    private $full;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
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
