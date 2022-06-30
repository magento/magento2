<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Test\Unit;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class IndexerRegistryTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetCreatesIndexerInstancesAndReusesExistingOnes(): void
    {
        $firstIndexer = $this->getMockForAbstractClass(IndexerInterface::class);
        $firstIndexer->expects($this->once())->method('load')->with('first-indexer')->willReturnSelf();

        $secondIndexer = $this->getMockForAbstractClass(IndexerInterface::class);
        $secondIndexer->expects($this->once())->method('load')->with('second-indexer')->willReturnSelf();

        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManager
            ->method('create')
            ->willReturnOnConsecutiveCalls($firstIndexer, $secondIndexer);

        $unit = new IndexerRegistry($objectManager);
        $this->assertSame($firstIndexer, $unit->get('first-indexer'));
        $this->assertSame($secondIndexer, $unit->get('second-indexer'));
        $this->assertSame($firstIndexer, $unit->get('first-indexer'));
    }
}
