<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheEnableCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CacheEnableCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManager;

    /**
     * @var CacheEnableCommand
     */
    private $command;

    public function setUp()
    {
        $this->cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
        $this->command = new CacheEnableCommand($this->cacheManager);
    }

    public function testExecute()
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManager
            ->expects($this->once())
            ->method('setEnabled')
            ->with(['A', 'B'], true)
            ->willReturn(['A', 'B']);
        $this->cacheManager->expects($this->once())->method('clean')->with(['A', 'B']);
        $param = ['types' => ['A', 'B']];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $expect = 'Changed cache status:' . PHP_EOL;
        foreach (['A', 'B'] as $cacheType) {
            $expect .= sprintf('%30s: %d -> %d', $cacheType, false, true) . PHP_EOL;
        }
        $expect .= 'Cleaned cache types:' . PHP_EOL;
        $expect .= 'A' . PHP_EOL . 'B' . PHP_EOL;
        $this->assertEquals($expect, $commandTester->getDisplay());
    }

    public function testExecuteAll()
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManager
            ->expects($this->once())
            ->method('setEnabled')
            ->with(['A', 'B', 'C'], true)
            ->willReturn(['A', 'B', 'C']);
        $this->cacheManager->expects($this->once())->method('clean')->with(['A', 'B', 'C']);
        $param = ['--all' => true];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $expect = 'Changed cache status:' . PHP_EOL;
        foreach (['A', 'B', 'C'] as $cacheType) {
            $expect .= sprintf('%30s: %d -> %d', $cacheType, false, true) . PHP_EOL;
        }
        $expect .= 'Cleaned cache types:' . PHP_EOL;
        $expect .= 'A' . PHP_EOL . 'B' . PHP_EOL . 'C' . PHP_EOL;
        $this->assertEquals($expect, $commandTester->getDisplay());
    }

    public function testExecuteNoChanges()
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManager
            ->expects($this->once())
            ->method('setEnabled')
            ->with(['A', 'B'], true)
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
