<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Indexer\Console\Command\IndexerShowModeCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerShowModeCommandTest extends IndexerCommandCommonTestSetup
{
    /**
     * Command being tested
     *
     * @var IndexerShowModeCommand
     */
    private $command;

    public function testGetOptions()
    {
        $this->command = new IndexerShowModeCommand($this->objectManagerFactory);
        $optionsList = $this->command->getInputList();
        $this->assertSame(2, sizeof($optionsList));
        $this->assertSame('all', $optionsList[0]->getName());
        $this->assertSame('index', $optionsList[1]->getName());
    }

    public function testExecuteAll()
    {

        $collection = $this->getMock('Magento\Indexer\Model\Indexer\Collection', [], [], '', false);
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $indexerOne->expects($this->once())->method('isScheduled')->willReturn(true);
        $indexerTwo = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexerTwo->expects($this->once())->method('getTitle')->willReturn('Title_indexerTwo');
        $indexerTwo->expects($this->once())->method('isScheduled')->willReturn(false);
        $collection->expects($this->once())->method('getItems')->willReturn([$indexerOne, $indexerTwo]);

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->indexerFactory->expects($this->never())->method('create');

        $this->command = new IndexerShowModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $expectedValue = sprintf('%-50s ', 'Title_indexerOne' . ':') . 'Update by Schedule' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexerTwo' . ':') . 'Update on Save';
        $this->assertStringStartsWith($expectedValue, $actualValue);
    }

    public function testExecuteWithIndex()
    {
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $indexerOne->expects($this->once())->method('isScheduled')->willReturn(true);
        $indexerTwo = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexerTwo->expects($this->once())->method('getTitle')->willReturn('Title_indexerTwo');
        $indexerTwo->expects($this->once())->method('isScheduled')->willReturn(false);
        $indexerThree = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexerThree->expects($this->never())->method('getTitle')->willReturn('Title_indexer3');
        $indexerThree->expects($this->never())->method('isScheduled')->willReturn(false);

        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->at(0))->method('create')->willReturn($indexerOne);
        $this->indexerFactory->expects($this->at(1))->method('create')->willReturn($indexerTwo);

        $this->command = new IndexerShowModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexerOne', 'id_indexerTwo']]);
        $actualValue = $commandTester->getDisplay();
        $expectedValue = sprintf('%-50s ', 'Title_indexerOne' . ':') . 'Update by Schedule' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexerTwo' . ':') . 'Update on Save';
        $this->assertStringStartsWith($expectedValue, $actualValue);
    }
}
