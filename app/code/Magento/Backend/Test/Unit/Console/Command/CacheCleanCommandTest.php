<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheCleanCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Tester\CommandTester;

class CacheCleanCommandTest extends AbstractCacheManageCommandTestCase
{
    protected function setUp(): void
    {
        $this->cacheEventName = 'adminhtml_cache_flush_system';
        parent::setUp();
        $this->command = new CacheCleanCommand(
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
        $this->cacheManagerMock->expects($this->once())->method('clean')->with($types);

        if ($shouldDispatch) {
            $this->eventManagerMock->expects($this->once())->method('dispatch')->with($this->cacheEventName);
        } else {
            $this->eventManagerMock->expects($this->never())->method('dispatch');
        }

        $this->warmupRunnerMock->expects($this->never())->method('run');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $this->assertEquals($output, $commandTester->getDisplay());
    }

    /**
     * Get expected output based on set of types operated on
     *
     * @param array $types
     * @return string
     */
    public static function getExpectedExecutionOutput(array $types)
    {
        return 'Cleaned cache types:' . PHP_EOL . implode(PHP_EOL, $types) . PHP_EOL;
    }

    public function testExecuteWithWarmupOption(): void
    {
        $this->cacheManagerMock->expects($this->once())->method('getAvailableTypes')->willReturn([
            'A', 'B', 'C', 'full_page'
        ]);
        $this->cacheManagerMock->expects($this->once())->method('clean')->with(['A', 'B', 'C', 'full_page']);
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
        $this->assertStringContainsString('Cleaned cache types:', $display);
        $this->assertStringContainsString('warmup_output_marker', $display);
    }
}
