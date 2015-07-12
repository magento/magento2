<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheStatusCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CacheStatusCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheManager;

    /**
     * @var CacheStatusCommand
     */
    private $command;

    public function setUp()
    {
        $this->cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
        $this->command = new CacheStatusCommand($this->cacheManager);
    }

    public function testExecute()
    {
        $cacheTypes = ['A' => 0, 'B' => 1, 'C' => 1];
        $this->cacheManager->expects($this->once())->method('getStatus')->willReturn($cacheTypes);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $expect = 'Current status:' . PHP_EOL;
        foreach ($cacheTypes as $cacheType => $status) {
            $expect .= sprintf('%30s: %d', $cacheType, $status) . PHP_EOL;
        }
        $this->assertEquals($expect, $commandTester->getDisplay());
    }
}
