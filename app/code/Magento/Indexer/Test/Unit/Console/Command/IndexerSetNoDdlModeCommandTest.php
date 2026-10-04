<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Indexer\Console\Command\IndexerSetNoDdlModeCommand;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Indexer\Model\NoDdlModeSupport;
use Magento\Framework\Indexer\NoDdlModeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerSetNoDdlModeCommandTest extends TestCase
{
    /**
     * @var IndexerFactory|MockObject
     */
    private $indexerFactoryMock;

    /**
     * @var NoDdlModeInterface|MockObject
     */
    private $noDdlModeMock;

    /**
     * @var NoDdlModeSupport|MockObject
     */
    private $noDdlModeSupportMock;

    /**
     * @var Indexer|MockObject
     */
    private $indexerMock;

    /**
     * @var IndexerSetNoDdlModeCommand
     */
    private $command;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->indexerFactoryMock = $this->createMock(IndexerFactory::class);
        $this->noDdlModeMock = $this->createMock(NoDdlModeInterface::class);
        $this->noDdlModeSupportMock = $this->createMock(NoDdlModeSupport::class);
        $this->indexerMock = $this->createMock(Indexer::class);

        $this->indexerFactoryMock->method('create')->willReturn($this->indexerMock);
        $this->noDdlModeSupportMock->method('isSupported')->willReturn(true);

        $this->command = new IndexerSetNoDdlModeCommand(
            $this->indexerFactoryMock,
            $this->noDdlModeMock,
            $this->noDdlModeSupportMock
        );
    }

    /**
     * @return void
     */
    public function testEnableSucceedsWhenIndexerIsScheduled(): void
    {
        $this->noDdlModeSupportMock->method('getPairedIndexerIds')->with('sample_indexer')->willReturn([]);
        $this->indexerMock->method('load')->with('sample_indexer')->willReturnSelf();
        $this->indexerMock->method('isScheduled')->willReturn(true);
        $this->indexerMock->method('getTitle')->willReturn('Sample Indexer');
        $this->indexerMock->method('getId')->willReturn('sample_indexer');

        $this->noDdlModeMock->expects($this->once())
            ->method('setEnabled')
            ->with('sample_indexer', true);
        $this->indexerMock->expects($this->once())->method('invalidate');

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'sample_indexer', 'mode' => 'enable']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode enabled for 'Sample Indexer'." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testEnableFailsWhenIndexerIsNotScheduled(): void
    {
        $this->indexerMock->method('load')->willReturnSelf();
        $this->indexerMock->method('isScheduled')->willReturn(false);
        $this->indexerMock->method('getId')->willReturn('sample_indexer');

        $this->noDdlModeMock->expects($this->never())->method('setEnabled');

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'sample_indexer', 'mode' => 'enable']);

        $this->assertSame(Cli::RETURN_FAILURE, $exitCode);
        $this->assertSame(
            'No-DDL reindex mode can only be enabled for indexers using "Update on Schedule" mode.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testEnableFailsWhenIndexerDoesNotSupportNoDdlMode(): void
    {
        $this->noDdlModeSupportMock = $this->createMock(NoDdlModeSupport::class);
        $this->noDdlModeSupportMock->method('isSupported')->willReturn(false);
        $this->command = new IndexerSetNoDdlModeCommand(
            $this->indexerFactoryMock,
            $this->noDdlModeMock,
            $this->noDdlModeSupportMock
        );
        $this->indexerMock->method('load')->willReturnSelf();
        $this->indexerMock->method('getId')->willReturn('unsupported_indexer');
        $this->indexerMock->method('getTitle')->willReturn('Unsupported Indexer');

        $this->noDdlModeMock->expects($this->never())->method('setEnabled');

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'unsupported_indexer', 'mode' => 'enable']);

        $this->assertSame(Cli::RETURN_FAILURE, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode is not supported for 'Unsupported Indexer'." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testEnableWarnsWhenPairedIndexerIsNotEnabled(): void
    {
        $this->noDdlModeSupportMock->method('getPairedIndexerIds')
            ->with('sample_indexer_a')
            ->willReturn(['sample_indexer_b']);
        $this->indexerMock->method('load')->with('sample_indexer_a')->willReturnSelf();
        $this->indexerMock->method('isScheduled')->willReturn(true);
        $this->indexerMock->method('getTitle')->willReturn('Sample Indexer A');
        $this->indexerMock->method('getId')->willReturn('sample_indexer_a');
        $this->noDdlModeMock->method('isEnabled')->with('sample_indexer_b')->willReturn(false);

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'sample_indexer_a', 'mode' => 'enable']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode enabled for 'Sample Indexer A'." . PHP_EOL
            . "Note: also run 'bin/magento indexer:set-no-ddl-mode sample_indexer_b enable' "
            . 'for this to take effect.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testEnableDoesNotWarnWhenPairedIndexerIsAlreadyEnabled(): void
    {
        $this->noDdlModeSupportMock->method('getPairedIndexerIds')
            ->with('sample_indexer_a')
            ->willReturn(['sample_indexer_b']);
        $this->indexerMock->method('load')->with('sample_indexer_a')->willReturnSelf();
        $this->indexerMock->method('isScheduled')->willReturn(true);
        $this->indexerMock->method('getTitle')->willReturn('Sample Indexer A');
        $this->indexerMock->method('getId')->willReturn('sample_indexer_a');
        $this->noDdlModeMock->method('isEnabled')->with('sample_indexer_b')->willReturn(true);

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'sample_indexer_a', 'mode' => 'enable']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode enabled for 'Sample Indexer A'." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testDisableAlwaysSucceeds(): void
    {
        $this->noDdlModeSupportMock->method('getPairedIndexerIds')->with('sample_indexer')->willReturn([]);
        $this->indexerMock->method('load')->willReturnSelf();
        $this->indexerMock->method('isScheduled')->willReturn(false);
        $this->indexerMock->method('getTitle')->willReturn('Sample Indexer');
        $this->indexerMock->method('getId')->willReturn('sample_indexer');

        $this->noDdlModeMock->expects($this->once())
            ->method('setEnabled')
            ->with('sample_indexer', false);
        $this->indexerMock->expects($this->once())->method('invalidate');

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'sample_indexer', 'mode' => 'disable']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode disabled for 'Sample Indexer'." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testDisableWarnsWhenPairedIndexerIsStillEnabled(): void
    {
        $this->noDdlModeSupportMock->method('getPairedIndexerIds')
            ->with('sample_indexer_a')
            ->willReturn(['sample_indexer_b']);
        $this->indexerMock->method('load')->with('sample_indexer_a')->willReturnSelf();
        $this->indexerMock->method('isScheduled')->willReturn(false);
        $this->indexerMock->method('getTitle')->willReturn('Sample Indexer A');
        $this->indexerMock->method('getId')->willReturn('sample_indexer_a');
        $this->noDdlModeMock->method('isEnabled')
            ->willReturnMap([
                ['sample_indexer_a', false],
                ['sample_indexer_b', true],
            ]);

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'sample_indexer_a', 'mode' => 'disable']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode disabled for 'Sample Indexer A'." . PHP_EOL
            . "Note: 'sample_indexer_b' is still enabled but will have no effect until "
            . "'sample_indexer_a' is enabled again." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testDisableDoesNotWarnWhenPairedIndexerIsAlsoDisabled(): void
    {
        $this->noDdlModeSupportMock->method('getPairedIndexerIds')
            ->with('sample_indexer_a')
            ->willReturn(['sample_indexer_b']);
        $this->indexerMock->method('load')->with('sample_indexer_a')->willReturnSelf();
        $this->indexerMock->method('isScheduled')->willReturn(false);
        $this->indexerMock->method('getTitle')->willReturn('Sample Indexer A');
        $this->indexerMock->method('getId')->willReturn('sample_indexer_a');
        $this->noDdlModeMock->method('isEnabled')
            ->willReturnMap([
                ['sample_indexer_a', false],
                ['sample_indexer_b', false],
            ]);

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'sample_indexer_a', 'mode' => 'disable']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode disabled for 'Sample Indexer A'." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testStatusReportsEnabledState(): void
    {
        $this->indexerMock->method('load')->willReturnSelf();
        $this->indexerMock->method('getTitle')->willReturn('Sample Indexer');
        $this->noDdlModeMock->expects($this->once())
            ->method('isEnabled')
            ->with('sample_indexer')
            ->willReturn(true);

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'sample_indexer', 'mode' => 'status']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode for 'Sample Indexer' is currently enabled." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testStatusReportsDisabledState(): void
    {
        $this->indexerMock->method('load')->willReturnSelf();
        $this->indexerMock->method('getTitle')->willReturn('Sample Indexer');
        $this->noDdlModeMock->expects($this->once())
            ->method('isEnabled')
            ->with('sample_indexer')
            ->willReturn(false);

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'sample_indexer']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode for 'Sample Indexer' is currently disabled." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testUnknownIndexerIdPropagatesException(): void
    {
        $this->indexerMock->method('load')
            ->with('bogus_indexer')
            ->willThrowException(new \InvalidArgumentException('bogus_indexer indexer does not exist.'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('bogus_indexer indexer does not exist.');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['indexer' => 'bogus_indexer', 'mode' => 'status']);
    }

    /**
     * @return void
     */
    public function testInvalidModeArgumentFails(): void
    {
        $this->indexerMock->method('load')->willReturnSelf();

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'sample_indexer', 'mode' => 'bogus']);

        $this->assertSame(Cli::RETURN_FAILURE, $exitCode);
        $this->assertSame(
            'Invalid mode "bogus". Accepted values: enable, disable, status.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }
}
