<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit;

class IndexerRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCreatesIndexerInstancesAndReusesExistingOnes()
    {
        $firstIndexer = $this->getMock(\Magento\Framework\Indexer\IndexerInterface::class);
        $firstIndexer->expects($this->once())->method('load')->with('first-indexer')->willReturnSelf();

        $secondIndexer = $this->getMock(\Magento\Framework\Indexer\IndexerInterface::class);
        $secondIndexer->expects($this->once())->method('load')->with('second-indexer')->willReturnSelf();

        $objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManager->expects($this->at(0))->method('create')->willReturn($firstIndexer);
        $objectManager->expects($this->at(1))->method('create')->willReturn($secondIndexer);

        $unit = new \Magento\Framework\Indexer\IndexerRegistry($objectManager);
        $this->assertSame($firstIndexer, $unit->get('first-indexer'));
        $this->assertSame($secondIndexer, $unit->get('second-indexer'));
        $this->assertSame($firstIndexer, $unit->get('first-indexer'));
    }
}
