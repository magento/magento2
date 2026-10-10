<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
        $firstIndexer = $this->createMock(IndexerInterface::class);
        $firstIndexer->expects($this->once())->method('load')->with('first-indexer')->willReturnSelf();

        $secondIndexer = $this->createMock(IndexerInterface::class);
        $secondIndexer->expects($this->once())->method('load')->with('second-indexer')->willReturnSelf();

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager
            ->method('create')
            ->willReturnOnConsecutiveCalls($firstIndexer, $secondIndexer);

        $unit = new IndexerRegistry($objectManager);
        $this->assertSame($firstIndexer, $unit->get('first-indexer'));
        $this->assertSame($secondIndexer, $unit->get('second-indexer'));
        $this->assertSame($firstIndexer, $unit->get('first-indexer'));
    }
}
