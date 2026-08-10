<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Console\Cli;
use Magento\Indexer\Console\Command\IndexerSetNoDdlModeCommand;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\IndexerFactory;
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
     * @var ConfigInterface|MockObject
     */
    private $configWriterMock;

    /**
     * @var TypeListInterface|MockObject
     */
    private $cacheTypeListMock;

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
        $this->configWriterMock = $this->createMock(ConfigInterface::class);
        $this->cacheTypeListMock = $this->createMock(TypeListInterface::class);
        $this->indexerMock = $this->createMock(Indexer::class);

        $this->indexerFactoryMock->method('create')->willReturn($this->indexerMock);

        $this->command = new IndexerSetNoDdlModeCommand(
            $this->indexerFactoryMock,
            $this->noDdlModeMock,
            $this->configWriterMock,
            $this->cacheTypeListMock
        );
    }

    /**
     * @return void
     */
    public function testEnableSucceedsWhenIndexerIsScheduled(): void
    {
        $this->indexerMock->method('load')->with('catalogpermissions_category')->willReturnSelf();
        $this->indexerMock->method('isScheduled')->willReturn(true);
        $this->indexerMock->method('getTitle')->willReturn('Category Permissions');
        $this->indexerMock->method('getId')->willReturn('catalogpermissions_category');

        $this->configWriterMock->expects($this->once())
            ->method('saveConfig')
            ->with('indexer/catalogpermissions_category/no_ddl_reindex', 1);
        $this->cacheTypeListMock->expects($this->once())->method('cleanType')->with('config');
        $this->indexerMock->expects($this->once())->method('invalidate');

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'catalogpermissions_category', 'mode' => 'enable']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode enabled for 'Category Permissions'." . PHP_EOL,
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

        $this->configWriterMock->expects($this->never())->method('saveConfig');

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'catalogpermissions_category', 'mode' => 'enable']);

        $this->assertSame(Cli::RETURN_FAILURE, $exitCode);
        $this->assertSame(
            'No-DDL reindex mode can only be enabled for indexers using "Update on Schedule" mode.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testDisableAlwaysSucceeds(): void
    {
        $this->indexerMock->method('load')->willReturnSelf();
        $this->indexerMock->method('isScheduled')->willReturn(false);
        $this->indexerMock->method('getTitle')->willReturn('Category Permissions');
        $this->indexerMock->method('getId')->willReturn('catalogpermissions_category');

        $this->configWriterMock->expects($this->once())
            ->method('saveConfig')
            ->with('indexer/catalogpermissions_category/no_ddl_reindex', 0);
        $this->cacheTypeListMock->expects($this->once())->method('cleanType')->with('config');
        $this->indexerMock->expects($this->once())->method('invalidate');

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'catalogpermissions_category', 'mode' => 'disable']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode disabled for 'Category Permissions'." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testStatusReportsEnabledState(): void
    {
        $this->indexerMock->method('load')->willReturnSelf();
        $this->indexerMock->method('getTitle')->willReturn('Category Permissions');
        $this->noDdlModeMock->expects($this->once())
            ->method('isEnabled')
            ->with('catalogpermissions_category')
            ->willReturn(true);

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'catalogpermissions_category', 'mode' => 'status']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode for 'Category Permissions' is currently enabled." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testStatusReportsDisabledState(): void
    {
        $this->indexerMock->method('load')->willReturnSelf();
        $this->indexerMock->method('getTitle')->willReturn('Category Permissions');
        $this->noDdlModeMock->expects($this->once())
            ->method('isEnabled')
            ->with('catalogpermissions_category')
            ->willReturn(false);

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'catalogpermissions_category']);

        $this->assertSame(Cli::RETURN_SUCCESS, $exitCode);
        $this->assertSame(
            "No-DDL reindex mode for 'Category Permissions' is currently disabled." . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testUnknownIndexerIdFailsGracefully(): void
    {
        $this->indexerMock->method('load')
            ->with('bogus_indexer')
            ->willThrowException(new \InvalidArgumentException('bogus_indexer indexer does not exist.'));

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'bogus_indexer', 'mode' => 'status']);

        $this->assertSame(Cli::RETURN_FAILURE, $exitCode);
        $this->assertSame(
            'bogus_indexer indexer does not exist.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }

    /**
     * @return void
     */
    public function testInvalidModeArgumentFails(): void
    {
        $this->indexerMock->method('load')->willReturnSelf();

        $commandTester = new CommandTester($this->command);
        $exitCode = $commandTester->execute(['indexer' => 'catalogpermissions_category', 'mode' => 'bogus']);

        $this->assertSame(Cli::RETURN_FAILURE, $exitCode);
        $this->assertSame(
            'Invalid mode "bogus". Accepted values: enable, disable, status.' . PHP_EOL,
            $commandTester->getDisplay()
        );
    }
}
