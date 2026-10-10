<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheWarmupCommand;
use Magento\Backend\Model\Cache\WarmupRunner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CacheWarmupCommandTest extends TestCase
{
    public function testExecuteRunsWarmupRunner(): void
    {
        $warmupRunner = $this->createMock(WarmupRunner::class);
        $warmupRunner->expects($this->once())->method('run')->willReturn(2);

        $command = new CacheWarmupCommand($warmupRunner);
        $tester = new CommandTester($command);
        $exit = $tester->execute([]);

        $this->assertSame(0, $exit);
    }
}
