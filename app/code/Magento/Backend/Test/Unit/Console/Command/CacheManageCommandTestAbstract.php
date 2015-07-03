<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\AbstractCacheManageCommand;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CacheManageCommandTestAbstract extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheManager;

    /**
     * @var AbstractCacheManageCommand
     */
    protected $command;

    public function setUp()
    {
        $this->cacheManager = $this->getMock('Magento\Framework\App\Cache\Manager', [], [], '', false);
    }

    /**
     * Formats expected output of cache status change
     *
     * @param array $changes
     * @param bool $enabled
     * @return string
     */
    public function getExpectedChangeOutput(array $changes, $enabled)
    {
        if ($changes) {
            $output = 'Changed cache status:' . PHP_EOL;
            foreach ($changes as $type) {
                $output .= sprintf('%30s: %d -> %d', $type, $enabled === false, $enabled === true) . PHP_EOL;
            }
        } else {
            $output = 'There is nothing to change in cache status' . PHP_EOL;
        }
        return $output;
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
