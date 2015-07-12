<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheFlushCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CacheFlushCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManager;

    /**
     * @var CacheFlushCommand
     */
    private $command;

    public function setUp()
    {
        $this->cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
        $this->command = new CacheFlushCommand($this->cacheManager);
    }

    public function testExecute()
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManager->expects($this->once())->method('flush')->with(['A', 'B']);
        $param = ['types' => ['A', 'B']];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);
        $expect = 'Flushed cache types:' . PHP_EOL . 'A' . PHP_EOL . 'B' . PHP_EOL;
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

    public function testExecuteAllCacheType()
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManager->expects($this->once())->method('flush')->with(['A', 'B', 'C']);
        $param = ['--all' => true];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);
        $expect = 'Flushed cache types:' . PHP_EOL . 'A' . PHP_EOL . 'B' . PHP_EOL . 'C' . PHP_EOL;
        $this->assertEquals($expect, $commandTester->getDisplay());
    }
}
