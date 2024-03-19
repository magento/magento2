<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product;

use Magento\Catalog\Model\Indexer\Product\Full;
use Magento\Framework\Indexer\ConfigInterface;
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

    /**
     * @var string[]
     */
    private $indexerList;

    /**
     * @var string[]
     */
    private $orderedIndexerList;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->indexerRegistryMock = $this->createMock(IndexerRegistry::class);
        $this->indexerList = ['search_indexer', 'eav_indexer', 'product_indexer', 'stock_indexer', 'invalid'];
        $this->orderedIndexerList = [
            'eav_indexer',
            'product_indexer',
            'stock_indexer',
            'price_indexer',
            'search_indexer'
        ];
        $this->configMock = $this->createMock(ConfigInterface::class);

        $this->full = new Full(
            $this->indexerRegistryMock,
            $this->indexerList,
            $this->configMock
        );
    }

    public function testExecuteFull()
    {
        $this->configMock->method('getIndexers')->willReturn(array_flip($this->orderedIndexerList));
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexAll');
        $orderedIndexerList = array_intersect($this->orderedIndexerList, $this->indexerList);
        $this->indexerRegistryMock->expects($this->exactly(4))
            ->method('get')
            ->with(
                // workaround for deprecated method withConsecutive
                $this->callback(
                    static function ($indexerName) use (&$orderedIndexerList) {
                        return $indexerName === array_shift($orderedIndexerList);
                    }
                )
            )
            ->willReturn($indexerMock);

        $this->full->executeFull();
    }

    public function testExecuteList()
    {
        $this->configMock->method('getIndexers')->willReturn(array_flip($this->orderedIndexerList));
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexList')->with([1, 2]);
        $orderedIndexerList = array_intersect($this->orderedIndexerList, $this->indexerList);
        $this->indexerRegistryMock->expects($this->exactly(4))
            ->method('get')
            ->with(
                // workaround for deprecated method withConsecutive
                $this->callback(
                    static function ($indexerName) use (&$orderedIndexerList) {
                        return $indexerName === array_shift($orderedIndexerList);
                    }
                )
            )
            ->willReturn($indexerMock);

        $this->full->executeList([1, 2]);
    }

    public function testExecuteRow()
    {
        $this->configMock->method('getIndexers')->willReturn(array_flip($this->orderedIndexerList));
        $indexerMock = $this->getMockForAbstractClass(IndexerInterface::class, [], "", false);
        $indexerMock->expects($this->exactly(4))->method('isScheduled')->willReturn(false);
        $indexerMock->expects($this->exactly(4))->method('reindexRow')->with(1);
        $orderedIndexerList = array_intersect($this->orderedIndexerList, $this->indexerList);
        $this->indexerRegistryMock->expects($this->exactly(4))
            ->method('get')
            ->with(
                // workaround for deprecated method withConsecutive
                $this->callback(
                    static function ($indexerName) use (&$orderedIndexerList) {
                        return $indexerName === array_shift($orderedIndexerList);
                    }
                )
            )
            ->willReturn($indexerMock);

        $this->full->executeRow(1);
    }
}
