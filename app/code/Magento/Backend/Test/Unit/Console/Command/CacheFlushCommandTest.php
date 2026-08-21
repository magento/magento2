<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheFlushCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Tester\CommandTester;

class CacheFlushCommandTest extends AbstractCacheManageCommandTestCase
{
    protected function setUp(): void
    {
        $this->cacheEventName = 'adminhtml_cache_flush_all';
        parent::setUp();
        $this->command = new CacheFlushCommand(
            $this->cacheManagerMock,
            $this->eventManagerMock,
            $this->warmupRunnerMock
        );
    }

    /**
     * @param array $param
     * @param array $types
     * @param bool $shouldDispatch
     * @param string $output
     */
    #[DataProvider('executeDataProvider')]
    public function testExecute($param, $types, $shouldDispatch, $output)
    {
        $this->cacheManagerMock->expects($this->once())->method('getAvailableTypes')->willReturn([
            'A', 'B', 'C', 'full_page'
        ]);
        $this->cacheManagerMock->expects($this->once())->method('flush')->with($types);

        if ($shouldDispatch) {
            $this->eventManagerMock->expects($this->once())->method('dispatch')->with($this->cacheEventName);
        } else {
            $this->eventManagerMock->expects($this->never())->method('dispatch');
        }

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $this->assertEquals($output, $commandTester->getDisplay());
    }

    /**
     * {@inheritdoc}
     */
    public static function getExpectedExecutionOutput(array $types)
    {
        return 'Flushed cache types:' . PHP_EOL . implode(PHP_EOL, $types) . PHP_EOL;
    }

    public function testExecuteWithWarmupOption(): void
    {
        $this->cacheManagerMock->expects($this->once())->method('getAvailableTypes')->willReturn([
            'A', 'B', 'C', 'full_page'
        ]);
        $this->cacheManagerMock->expects($this->once())->method('flush')->with(['A', 'B', 'C', 'full_page']);
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with($this->cacheEventName);
        $this->warmupRunnerMock->expects($this->once())->method('run')->willReturnCallback(
            function ($output) {
                $output->writeln('warmup_output_marker');
                return 1;
            }
        );

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['--warmup' => true]);

        $display = $commandTester->getDisplay();
        $this->assertStringContainsString('Flushed cache types:', $display);
        $this->assertStringContainsString('warmup_output_marker', $display);
    }
}
