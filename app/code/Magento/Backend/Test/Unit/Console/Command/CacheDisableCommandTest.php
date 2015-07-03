<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheDisableCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CacheDisableManageCommandTest extends CacheManageCommandTestAbstract
{
    /**
     * @var AbstractCacheManageCommand
     */
    protected $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new CacheDisableCommand($this->cacheManager);
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
        $this->cacheManager->expects($this->once())->method('setEnabled')->with($enable, false)->willReturn($result);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $this->assertEquals($output, $commandTester->getDisplay());
    }

    public function testExecuteDataProvider ()
    {
        return [
            'no parameters' => [
                [],
                ['A', 'B', 'C'],
                ['A', 'B', 'C'],
                $this->getExpectedChangeOutput(['A', 'B', 'C'], false),
            ],
            'explicit --all' => [
                ['--all' => true, 'types' => 'A'],
                ['A', 'B', 'C'],
                ['A', 'B', 'C'],
                $this->getExpectedChangeOutput(['A', 'B', 'C'], false),
            ],
            'specific types' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                ['A', 'B'],
                $this->getExpectedChangeOutput(['A', 'B'], false),
            ],
            'no changes' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                [],
                $this->getExpectedChangeOutput([], false),
            ],
        ];
    }
}
