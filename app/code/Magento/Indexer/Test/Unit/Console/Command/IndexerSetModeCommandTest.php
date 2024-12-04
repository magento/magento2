<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\Exception\LocalizedException;
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
        $this->assertCount(2, $optionsList);
        $this->assertSame('mode', $optionsList[0]->getName());
        $this->assertSame('index', $optionsList[1]->getName());
    }

    public function testExecuteInvalidArgument()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage("Missing argument 'mode'. Accepted values for mode are 'realtime' or 'schedule'");
        $this->stateMock->expects($this->never())->method('setAreaCode')->with(FrontNameResolver::AREA_CODE);
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    public function testExecuteInvalidMode()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Accepted values for mode are \'realtime\' or \'schedule\'');
        $this->stateMock->expects($this->never())->method('setAreaCode')->with(FrontNameResolver::AREA_CODE);
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'wrong_mode']);
    }

    public function testExecuteAll()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getIndexerMock(
            ['isScheduled', 'setScheduled'],
            ['indexer_id' => 'indexer_1', 'title' => 'Title_indexerOne']
        );

        $indexerOne->expects($this->exactly(2))
            ->method('isScheduled')
            ->willReturnCallback(function () use (&$callCount) {
                $callCount++;
                return $callCount === 1 ? true : false;
            });

        $indexerOne->expects($this->once())->method('setScheduled')->with(false);

        $this->initIndexerCollectionByItems([$indexerOne]);
        $this->indexerFactory->expects($this->never())->method('create');
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'realtime']);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame(
            'Index mode for Indexer Title_indexerOne was changed from ' . '\'Update by Schedule\' to \'Update on Save\''
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
        $indexerOne = $this->getIndexerMock(
            ['isScheduled', 'setScheduled'],
            ['indexer_id' => 'id_indexerOne', 'title' => 'Title_indexerOne']
        );
        $this->initIndexerCollectionByItems([$indexerOne]);
        $indexerOne->expects($this->once())->method('setScheduled')->with($isScheduled);
        $indexerOne->expects($this->exactly(2))
            ->method('isScheduled')
            ->willReturnOnConsecutiveCalls($previous, $current);

        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => $mode, 'index' => ['id_indexerOne']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame($expectedValue, $actualValue);
    }

    /**
     * @return array
     */
    public static function executeWithIndexDataProvider()
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
        $indexerOne = $this->getIndexerMock(
            ['isScheduled', 'setScheduled'],
            ['indexer_id' => 'id_indexerOne']
        );
        $localizedException = new LocalizedException(__('Some Exception Message'));
        $indexerOne->expects($this->once())->method('setScheduled')->willThrowException($localizedException);
        $this->initIndexerCollectionByItems([$indexerOne]);
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'schedule', 'index' => ['id_indexerOne']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Some Exception Message', $actualValue);
    }

    public function testExecuteWithException()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getIndexerMock(
            ['isScheduled', 'setScheduled'],
            ['indexer_id' => 'id_indexerOne', 'title' => 'Title_indexerOne']
        );
        $exception = new \Exception();
        $indexerOne->expects($this->once())->method('setScheduled')->willThrowException($exception);
        $this->initIndexerCollectionByItems([$indexerOne]);
        $this->command = new IndexerSetModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['mode' => 'schedule', 'index' => ['id_indexerOne']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexerOne indexer process unknown error:', $actualValue);
    }
}
