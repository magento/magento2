<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console;

use Magento\Indexer\Console\IndexerShowModeCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerShowModeCommandTest extends IndexerCommandCommonTestSetup
{
    /**
     * Command being tested
     *
     * @var IndexerInfoCommand
     */
    private $command;

    public function testGetOptions()
    {
        $this->command = new IndexerShowModeCommand($this->objectManagerFactory);
        $optionsList = $this->command->getOptionsList();
        $this->assertSame(2, sizeof($optionsList));
        $this->assertSame('all', $optionsList[0]->getName());
        $this->assertSame('index', $optionsList[1]->getName());
    }

    public function testExecuteAll()
    {

        $collection = $this->getMock('Magento\Indexer\Model\Indexer\Collection', [], [], '', false);
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer1->expects($this->once())->method('getTitle')->willReturn('Title_indexer1');
        $indexer1->expects($this->once())->method('isScheduled')->willReturn(true);
        $indexer2 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer2->expects($this->once())->method('getTitle')->willReturn('Title_indexer2');
        $indexer2->expects($this->once())->method('isScheduled')->willReturn(false);
        $collection->expects($this->once())->method('getItems')->willReturn([$indexer1, $indexer2]);

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->indexerFactory->expects($this->never())->method('create');

        $this->command = new IndexerShowModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $expectedValue = sprintf('%-50s ', 'Title_indexer1' . ':') . 'Update by Schedule' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexer2' . ':') . 'Update on Save';
        $this->assertStringStartsWith($expectedValue, $actualValue);
    }

    public function testExecuteWithIndex()
    {
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer1->expects($this->once())->method('getTitle')->willReturn('Title_indexer1');
        $indexer1->expects($this->once())->method('isScheduled')->willReturn(true);
        $indexer2 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer2->expects($this->once())->method('getTitle')->willReturn('Title_indexer2');
        $indexer2->expects($this->once())->method('isScheduled')->willReturn(false);
        $indexer3 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer3->expects($this->never())->method('getTitle')->willReturn('Title_indexer3');
        $indexer3->expects($this->never())->method('isScheduled')->willReturn(false);

        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->at(0))->method('create')->willReturn($indexer1);
        $this->indexerFactory->expects($this->at(1))->method('create')->willReturn($indexer2);

        $this->command = new IndexerShowModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexer1,id_indexer2']]);
        $actualValue = $commandTester->getDisplay();
        $expectedValue = sprintf('%-50s ', 'Title_indexer1' . ':') . 'Update by Schedule' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexer2' . ':') . 'Update on Save';
        $this->assertStringStartsWith($expectedValue, $actualValue);
    }
}
