<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheFlushCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CacheFlushCommandTest extends AbstractCacheManageCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->command = new CacheFlushCommand($this->cacheManager);
    }

    /**
     * @param array $param
     * @param array $types
     * @param string $output
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
     * {@inheritdoc}
     */
    public function getExpectedExecutionOutput(array $types)
    {
        return 'Flushed cache types:' . PHP_EOL . implode(PHP_EOL, $types) . PHP_EOL;
    }
}
