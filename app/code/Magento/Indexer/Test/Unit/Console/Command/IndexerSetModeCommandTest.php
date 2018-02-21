<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Indexer\Console\Command\IndexerSetModeCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Command for updating installed application after the code base has changed
 */
class IndexerSetModeCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerSetModeCommand
     */
    private $command;

    public function testGetOptions()
    {
        $this->stateMock->expects($this->never())->method('setAreaCode')->with(FrontNameResolver::AREA_CODE);
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $optionsList = $this->command->getInputList();
        $this->assertSame(2, sizeof($optionsList));
        $this->assertSame('mode', $optionsList[0]->getName());
        $this->assertSame('index', $optionsList[1]->getName());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing argument 'mode'. Accepted values for mode are 'realtime' or 'schedule'
     */
    public function testExecuteInvalidArgument()
    {
        $this->stateMock->expects($this->never())->method('setAreaCode')->with(FrontNameResolver::AREA_CODE);
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
        $this->stateMock->expects($this->never())->method('setAreaCode')->with(FrontNameResolver::AREA_CODE);
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'wrong_mode']);
    }

    public function testExecuteAll()
    {
        $this->configureAdminArea();
        $collection = $this->getMock('Magento\Indexer\Model\Indexer\Collection', [], [], '', false);
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);

        $indexerOne->expects($this->at(0))->method('isScheduled')->willReturn(true);
        $indexerOne->expects($this->at(2))->method('isScheduled')->willReturn(false);

        $indexerOne->expects($this->once())->method('setScheduled')->with(false);
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $collection->expects($this->once())->method('getItems')->willReturn([$indexerOne]);

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->indexerFactory->expects($this->never())->method('create');
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'realtime']);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame(
            'Index mode for Indexer Title_indexerOne was changed from '. '\'Update by Schedule\' to \'Update on Save\''
            . PHP_EOL,
            $actualValue
        );
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
        $this->configureAdminArea();
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $indexerOne->expects($this->once())->method('load')->with('id_indexerOne')->willReturn($indexerOne);
        $indexerOne->expects($this->once())->method('setScheduled')->with($isScheduled);
        $indexerOne->expects($this->at(1))->method('isScheduled')->willReturn($previous);
        $indexerOne->expects($this->at(3))->method('isScheduled')->willReturn($current);

        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexerOne);

        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => $mode, 'index' => ['id_indexerOne']]);
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
                'Index mode for Indexer Title_indexerOne was changed from \'Update by Schedule\' to \'Update on Save\''
                . PHP_EOL
            ],
            [
                false,
                false,
                false,
                'realtime',
                'Index mode for Indexer Title_indexerOne has not been changed'
                . PHP_EOL
            ],
            [
                true,
                true,
                true,
                'schedule',
                'Index mode for Indexer Title_indexerOne has not been changed'
                . PHP_EOL
            ],
            [
                true,
                false,
                true,
                'schedule',
                'Index mode for Indexer Title_indexerOne was changed from \'Update on Save\' to \'Update by Schedule\''
                . PHP_EOL
            ],
        ];
    }

    public function testExecuteWithLocalizedException()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $localizedException = new \Magento\Framework\Exception\LocalizedException(__('Some Exception Message'));
        $indexerOne->expects($this->once())->method('setScheduled')->will($this->throwException($localizedException));
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexerOne);
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'schedule', 'index' => ['id_indexerOne']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Some Exception Message', $actualValue);
    }

    public function testExecuteWithException()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $exception = new \Exception();
        $indexerOne->expects($this->once())->method('setScheduled')->will($this->throwException($exception));
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexerOne);
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'schedule', 'index' => ['id_indexerOne']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexerOne indexer process unknown error:', $actualValue);
    }
}
