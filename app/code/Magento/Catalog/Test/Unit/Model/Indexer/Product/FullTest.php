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
use Magento\PageCache\Model\Config;
use Magento\Framework\App\Cache\TypeListInterface;

class FullTest extends TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeListMock;

    /**
     * @var Full
     */
    private $full;
    
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->indexerRegistryMock = $this->createMock(IndexerRegistry::class);
        $this->configMock = $this->createMock(Config::class);
        $this->typeListMock = $this->getMockForAbstractClass(TypeListInterface::class, [], "", false);

        $this->full = $this->objectManager->getObject(
            Full::class,
            [
                'indexerRegistry' => $this->indexerRegistryMock,
                'pageCacheConfig' => $this->configMock,
                'cacheTypeList' => $this->typeListMock,
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
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->typeListMock->expects($this->once())->method('invalidate')->with('full_page');

        $this->full->executeFull();
    }

    public function testExecuteFullPageCacheDisabled()
    {
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexAll');
        $this->indexerRegistryMock->expects($this->exactly(4))->method('get')->willReturn($indexerMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->typeListMock->expects($this->never())->method('invalidate');

        $this->full->executeFull();
    }

    public function testExecuteList()
    {
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexList')->with([1, 2]);
        $this->indexerRegistryMock->expects($this->exactly(4))->method('get')->willReturn($indexerMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->typeListMock->expects($this->once())->method('invalidate')->with('full_page');

        $this->full->executeList([1, 2]);
    }

    public function testExecuteListPageCacheDisabled()
    {
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexList')->with([1, 2]);
        $this->indexerRegistryMock->expects($this->exactly(4))->method('get')->willReturn($indexerMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->typeListMock->expects($this->never())->method('invalidate');

        $this->full->executeList([1, 2]);
    }

    public function testExecuteRow()
    {
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexRow')->with(1);
        $this->indexerRegistryMock->expects($this->exactly(4))->method('get')->willReturn($indexerMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->typeListMock->expects($this->once())->method('invalidate')->with('full_page');

        $this->full->executeRow(1);
    }

    public function testExecuteRowPageCacheDisabled()
    {
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexRow')->with(1);
        $this->indexerRegistryMock->expects($this->exactly(4))->method('get')->willReturn($indexerMock);
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->typeListMock->expects($this->never())->method('invalidate');

        $this->full->executeRow(1);
    }
}
