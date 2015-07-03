<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheFlushCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CacheFlushManageCommandTest extends CacheManageCommandTestAbstract
{
    public function setUp()
    {
        parent::setUp();
        $this->command = new CacheFlushCommand($this->cacheManager);
    }

    /**
     * @param $param
     * @param $types
     * @param $output
     * @dataProvider testExecuteDataProvider
     */
    public function testExecute($param, $types, $output)
    {
        $this->cacheManager->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManager->expects($this->once())->method('flush')->with($types);

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
                $this->getExpectedOutput(['A', 'B', 'C']),
            ],
            'explicit --all' => [
                ['--all' => true, 'types' => 'A'],
                ['A', 'B', 'C'],
                $this->getExpectedOutput(['A', 'B', 'C']),
            ],
            'specific types' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                $this->getExpectedOutput(['A', 'B']),
            ],
        ];
    }

    /**
     * Get expected output based on set of types operated on
     *
     * @param array $types
     * @return string
     */
    public function getExpectedOutput(array $types)
    {
        return 'Flushed cache types:' . PHP_EOL . implode(PHP_EOL, $types) . PHP_EOL;
    }
}
