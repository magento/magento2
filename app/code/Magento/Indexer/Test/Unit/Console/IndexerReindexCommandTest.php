<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console;

use Magento\Indexer\Console\IndexerReindexCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerReindexCommandTest extends IndexerCommandCommonTestSetup
{
    /**
     * Command being tested
     *
     * @var IndexerReindexCommand
     */
    private $command;

    public function testGetOptions()
    {
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
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
        $collection->expects($this->once())->method('getItems')->willReturn([$indexer1]);

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->indexerFactory->expects($this->never())->method('create');
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexer1 index has been rebuilt successfully in', $actualValue);
    }

    public function testExecuteWithIndex()
    {
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer1->expects($this->once())->method('reindexAll');
        $indexer1->expects($this->once())->method('getTitle')->willReturn('Title_indexer1');
        $indexer1->expects($this->once())->method('load')->with('id_indexer1')->willReturn($indexer1);

        $indexer2 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer2->expects($this->once())->method('reindexAll');
        $indexer2->expects($this->once())->method('getTitle')->willReturn('Title_indexer2');
        $indexer2->expects($this->once())->method('load')->with('id_indexer2')->willReturn($indexer2);

        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->at(0))->method('create')->willReturn($indexer1);
        $this->indexerFactory->expects($this->at(1))->method('create')->willReturn($indexer2);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexer1,id_indexer2']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexer1 index has been rebuilt successfully in', $actualValue);
    }

    public function testExecuteWithLocalizedException()
    {
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $localizedException = new \Magento\Framework\Exception\LocalizedException(__('Some Exception Message'));
        $indexer1->expects($this->once())->method('reindexAll')->will($this->throwException($localizedException));
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexer1);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexer1']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Some Exception Message', $actualValue);
    }

    public function testExecuteWithException()
    {
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $exception = new \Exception();
        $indexer1->expects($this->once())->method('reindexAll')->will($this->throwException($exception));
        $indexer1->expects($this->once())->method('getTitle')->willReturn('Title_indexer1');
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexer1);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexer1']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexer1 indexer process unknown error:', $actualValue);
    }

    public function testExecuteWithExceptionInLoad()
    {
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $exception = new \Exception();
        $indexer1->expects($this->once())->method('load')->will($this->throwException($exception));
        $indexer1->expects($this->once())->method('getTitle')->willReturn('Title_indexer1');
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexer1);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexer1']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Warning: Unknown indexer with code', $actualValue);
    }
}
