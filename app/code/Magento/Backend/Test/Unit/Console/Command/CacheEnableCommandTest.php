<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheEnableCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CacheEnableManageCommandTest extends CacheManageCommandTestAbstract
{
    public function setUp()
    {
        parent::setUp();
        $this->command = new CacheEnableCommand($this->cacheManager);
    }

    /**
     * @param $param
     * @param $enable
     * @param $result
     * @param $output
     * @dataProvider testExecuteDataProvider
     */
    public function testExecute($param, $enable, $result, $output)
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManager->expects($this->once())->method('setEnabled')->with($enable, true)->willReturn($result);
        $this->cacheManager->expects($result == [] ? $this->never() : $this->once())->method('clean')->with($enable);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $this->assertEquals($output, $commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function testExecuteDataProvider()
    {
        return [
            'no parameters' => [
                [],
                ['A', 'B', 'C'],
                ['A', 'B', 'C'],
                $this->getExpectedEnableOutput(['A', 'B', 'C']),
            ],
            'explicit --all' => [
                ['--all' => true, 'types' => 'A'],
                ['A', 'B', 'C'],
                ['A', 'B', 'C'],
                $this->getExpectedEnableOutput(['A', 'B', 'C']),
            ],
            'specific types' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                ['A', 'B'],
                $this->getExpectedEnableOutput(['A', 'B']),
            ],
            'no changes' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                [],
                $this->getExpectedEnableOutput([]),
            ],
        ];
    }

    /**
     * Formats expected output string of cache enable command
     *
     * @param array $enabled
     * @return string
     */
    public function getExpectedEnableOutput(array $enabled)
    {
        $output = $this->getExpectedChangeOutput($enabled, true);
        if ($enabled) {
            $output .= 'Cleaned cache types:' . PHP_EOL;
            $output .= implode(PHP_EOL, $enabled) . PHP_EOL;
        }
        return $output;
    }
}
