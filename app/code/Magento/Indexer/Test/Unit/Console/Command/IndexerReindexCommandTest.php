<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Indexer\Console\Command\IndexerReindexCommand;
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
        $collection->expects($this->once())->method('getItems')->willReturn([$indexerOne]);

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->indexerFactory->expects($this->never())->method('create');
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexerOne index has been rebuilt successfully in', $actualValue);
    }

    public function testExecuteWithIndex()
    {
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexerOne->expects($this->once())->method('reindexAll');
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $indexerOne->expects($this->once())->method('load')->with('id_indexerOne')->willReturn($indexerOne);

        $indexerTwo = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexerTwo->expects($this->once())->method('reindexAll');
        $indexerTwo->expects($this->once())->method('getTitle')->willReturn('Title_indexerTwo');
        $indexerTwo->expects($this->once())->method('load')->with('id_indexerTwo')->willReturn($indexerTwo);

        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->at(0))->method('create')->willReturn($indexerOne);
        $this->indexerFactory->expects($this->at(1))->method('create')->willReturn($indexerTwo);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexerOne', 'id_indexerTwo']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexerOne index has been rebuilt successfully in', $actualValue);
    }

    public function testExecuteWithLocalizedException()
    {
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $localizedException = new \Magento\Framework\Exception\LocalizedException(__('Some Exception Message'));
        $indexerOne->expects($this->once())->method('reindexAll')->will($this->throwException($localizedException));
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexerOne);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexerOne']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Some Exception Message', $actualValue);
    }

    public function testExecuteWithException()
    {
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $exception = new \Exception();
        $indexerOne->expects($this->once())->method('reindexAll')->will($this->throwException($exception));
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexerOne);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexerOne']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexerOne indexer process unknown error:', $actualValue);
    }

    public function testExecuteWithExceptionInLoad()
    {
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $exception = new \Exception();
        $indexerOne->expects($this->once())->method('load')->will($this->throwException($exception));
        $indexerOne->expects($this->never())->method('getTitle');
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexerOne);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexerOne']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Warning: Unknown indexer with code', $actualValue);
    }
}
