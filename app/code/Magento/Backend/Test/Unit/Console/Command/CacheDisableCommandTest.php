<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheDisableCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CacheDisableCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManager;

    /**
     * @var CacheDisableCommand
     */
    private $command;

    public function setUp()
    {
        $this->cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
        $this->command = new CacheDisableCommand($this->cacheManager);
    }

    public function testExecute()
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManager
            ->expects($this->once())
            ->method('setEnabled')
            ->with(['A', 'B'], false)
            ->willReturn(['A', 'B']);
        $param = ['types' => ['A', 'B']];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $expect = 'Changed cache status:' . PHP_EOL;
        foreach (['A', 'B'] as $cacheType) {
            $expect .= sprintf('%30s: %d -> %d', $cacheType, true, false) . PHP_EOL;
        }

        $this->assertEquals($expect, $commandTester->getDisplay());
    }

    public function testExecuteAll()
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManager
            ->expects($this->once())
            ->method('setEnabled')
            ->with(['A', 'B', 'C'], false)
            ->willReturn(['A', 'B', 'C']);
        $param = ['--all' => true];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $expect = 'Changed cache status:' . PHP_EOL;
        foreach (['A', 'B', 'C'] as $cacheType) {
            $expect .= sprintf('%30s: %d -> %d', $cacheType, true, false) . PHP_EOL;
        }
        $this->assertEquals($expect, $commandTester->getDisplay());
    }

    public function testExecuteNoChanges()
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManager
            ->expects($this->once())
            ->method('setEnabled')
            ->with(['A', 'B'], false)
            ->willReturn([]);
        $this->cacheManager->expects($this->never())->method('clean');
        $param = ['types' => ['A', 'B']];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $expect = 'There is nothing to change in cache status' . PHP_EOL;
        $this->assertEquals($expect, $commandTester->getDisplay());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The following requested cache types are not supported:
     */
    public function testExecuteInvalidCacheType()
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $param = ['types' => ['A', 'D']];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);
    }
}
