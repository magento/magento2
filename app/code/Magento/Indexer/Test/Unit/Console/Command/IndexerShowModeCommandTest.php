<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Indexer\Console\Command\IndexerShowModeCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerShowModeCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerShowModeCommand
     */
    private $command;

    public function testGetOptions()
    {
        $this->stateMock->expects($this->never())->method('setAreaCode')->with(FrontNameResolver::AREA_CODE);
        $this->command = new IndexerShowModeCommand($this->objectManagerFactory);
        $optionsList = $this->command->getInputList();
        $this->assertSame(1, sizeof($optionsList));
        $this->assertSame('index', $optionsList[0]->getName());
    }

    public function testExecuteAll()
    {
        $this->configureAdminArea();
        $collection = $this->getMock(\Magento\Indexer\Model\Indexer\Collection::class, [], [], '', false);
        $indexerOne = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $indexerOne->expects($this->once())->method('isScheduled')->willReturn(true);
        $indexerTwo = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
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
        $this->configureAdminArea();
        $indexerOne = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $indexerOne->expects($this->once())->method('isScheduled')->willReturn(true);
        $indexerTwo = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
        $indexerTwo->expects($this->once())->method('getTitle')->willReturn('Title_indexerTwo');
        $indexerTwo->expects($this->once())->method('isScheduled')->willReturn(false);
        $indexerThree = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
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
