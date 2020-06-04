<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheCleanCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CacheCleanCommandTest extends AbstractCacheManageCommandTest
{
    protected function setUp(): void
    {
        $this->cacheEventName = 'adminhtml_cache_flush_system';
        parent::setUp();
        $this->command = new CacheCleanCommand($this->cacheManagerMock, $this->eventManagerMock);
    }

    /**
     * @param array $param
     * @param array $types
     * @param string $output
     * @dataProvider executeDataProvider
     */
    public function testExecute($param, $types, $output)
    {
        $this->cacheManagerMock->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManagerMock->expects($this->once())->method('clean')->with($types);
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with($this->cacheEventName);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $this->assertEquals($output, $commandTester->getDisplay());
    }

    /**
     * Get expected output based on set of types operated on
     *
     * @param array $types
     * @return string
     */
    public function getExpectedExecutionOutput(array $types)
    {
        return 'Cleaned cache types:' . PHP_EOL . implode(PHP_EOL, $types) . PHP_EOL;
    }
}
