<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Indexer\Console\Command\IndexerStatusCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerStatusCommandTest extends IndexerCommandCommonTestSetup
{
    /**
     * Command being tested
     *
     * @var IndexerStatusCommand
     */
    private $command;

    public function testExecuteAll()
    {
        $collection = $this->getMock('Magento\Indexer\Model\Indexer\Collection', [], [], '', false);
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer1->expects($this->once())->method('getTitle')->willReturn('Title_indexer1');
        $indexer1
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(\Magento\Indexer\Model\Indexer\State::STATUS_VALID);
        $indexer2 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer2->expects($this->once())->method('getTitle')->willReturn('Title_indexer2');
        $indexer2
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(\Magento\Indexer\Model\Indexer\State::STATUS_INVALID);
        $indexer3 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer3->expects($this->once())->method('getTitle')->willReturn('Title_indexer3');
        $indexer3
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(\Magento\Indexer\Model\Indexer\State::STATUS_WORKING);
        $indexer4 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer4->expects($this->once())->method('getTitle')->willReturn('Title_indexer4');
        $collection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$indexer1, $indexer2, $indexer3, $indexer4]);

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->indexerFactory->expects($this->never())->method('create');
        $this->command = new IndexerStatusCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $expectedValue = sprintf('%-50s ', 'Title_indexer1' . ':') . 'Ready' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexer2' . ':') . 'Reindex required' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexer3' . ':') . 'Processing' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexer4' . ':') . 'unknown' . PHP_EOL;


        $this->assertStringStartsWith($expectedValue, $actualValue);
    }
}
