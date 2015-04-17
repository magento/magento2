<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console;

use Magento\Indexer\Console\IndexerSetModeCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Command for updating installed application after the code base has changed
 */
class IndexerSetModeCommandTest extends IndexerCommandCommonTestSetup
{
    /**
     * Command being tested
     *
     * @var IndexerSetModeCommand
     */
    private $command;

    public function testGetOptions()
    {
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $optionsList = $this->command->getOptionsList();
        $this->assertSame(3, sizeof($optionsList));
        $this->assertSame('mode', $optionsList[0]->getName());
        $this->assertSame('all', $optionsList[1]->getName());
        $this->assertSame('index', $optionsList[2]->getName());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing argument 'mode'. Accepted values for mode are 'realtime' or 'schedule'
     */
    public function testExecuteInvalidArgument()
    {
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Accepted values for mode are 'realtime' or 'schedule'
     */
    public function testExecuteInvalidMode()
    {
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'wrong_mode']);
    }

    public function testExecuteAll()
    {
        $collection = $this->getMock('Magento\Indexer\Model\Indexer\Collection', [], [], '', false);
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);

        $indexer1->expects($this->at(0))->method('isScheduled')->willReturn(true);
        $indexer1->expects($this->at(2))->method('isScheduled')->willReturn(false);

        $indexer1->expects($this->once())->method('setScheduled')->with(false);
        $indexer1->expects($this->once())->method('getTitle')->willReturn('Title_indexer1');
        $collection->expects($this->once())->method('getItems')->willReturn([$indexer1]);

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->indexerFactory->expects($this->never())->method('create');
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'realtime']);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame('Index mode for Indexer Title_indexer1 was changed from \'Update by Schedule\' to \'Update on Save\'' . PHP_EOL, $actualValue);
    }

    /**
     * @param bool $isScheduled
     * @param bool $previous
     * @param bool $current
     * @param string $mode
     * @param $expectedValue
     * @dataProvider executeWithIndexDataProvider
     */
    public function testExecuteWithIndex($isScheduled, $previous, $current, $mode, $expectedValue)
    {
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer1->expects($this->once())->method('getTitle')->willReturn('Title_indexer1');
        $indexer1->expects($this->once())->method('load')->with('id_indexer1')->willReturn($indexer1);
        $indexer1->expects($this->once())->method('setScheduled')->with($isScheduled);
        $indexer1->expects($this->at(1))->method('isScheduled')->willReturn($previous);
        $indexer1->expects($this->at(3))->method('isScheduled')->willReturn($current);

        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexer1);

        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => $mode, 'index' => ['id_indexer1']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame($expectedValue, $actualValue);
    }

    /**
     * @return array
     */
    public function executeWithIndexDataProvider()
    {
        return [
            [
                false,
                true,
                false,
                'realtime',
                'Index mode for Indexer Title_indexer1 was changed from \'Update by Schedule\' to \'Update on Save\''
                . PHP_EOL
            ],
            [
                false,
                false,
                false,
                'realtime',
                'Index mode for Indexer Title_indexer1 has not been changed'
                . PHP_EOL
            ],
            [
                true,
                true,
                true,
                'schedule',
                'Index mode for Indexer Title_indexer1 has not been changed'
                . PHP_EOL
            ],
            [
                true,
                false,
                true,
                'schedule',
                'Index mode for Indexer Title_indexer1 was changed from \'Update on Save\' to \'Update by Schedule\''
                . PHP_EOL
            ],
        ];
    }
    public function testExecuteWithLocalizedException()
    {
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $localizedException = new \Magento\Framework\Exception\LocalizedException(__('Some Exception Message'));
        $indexer1->expects($this->once())->method('setScheduled')->will($this->throwException($localizedException));
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexer1);
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'schedule', 'index' => ['id_indexer1']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Some Exception Message', $actualValue);
    }

    public function testExecuteWithException()
    {
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $exception = new \Exception();
        $indexer1->expects($this->once())->method('setScheduled')->will($this->throwException($exception));
        $indexer1->expects($this->once())->method('getTitle')->willReturn('Title_indexer1');
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexer1);
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'schedule', 'index' => ['id_indexer1']]);;
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexer1 indexer process unknown error:', $actualValue);
    }
}
