<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\CacheEnableCommand;
use Symfony\Component\Console\Tester\CommandTester;

class CacheEnableCommandTest extends AbstractCacheSetCommandTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->command = new CacheEnableCommand($this->cacheManagerMock);
    }

    /**
     * @param array $param
     * @param array $enable
     * @param array $result
     * @param string $output
     * @dataProvider executeDataProvider
     */
    public function testExecute($param, $enable, $result, $output)
    {
        $this->cacheManagerMock->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $this->cacheManagerMock->expects($this->once())
            ->method('setEnabled')
            ->with($enable, true)
            ->willReturn($result);
        $cleanInvocationCount = $result === [] ? 0 : 1;
        $this->cacheManagerMock->expects($this->exactly($cleanInvocationCount))
            ->method('clean')
            ->with($enable);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);

        $this->assertEquals($output, $commandTester->getDisplay());
    }

    /**
     * {@inheritdoc}
     */
    public function getExpectedExecutionOutput(array $enabled)
    {
        $output = $this->getExpectedChangeOutput($enabled, true);
        if ($enabled) {
            $output .= 'Cleaned cache types:' . PHP_EOL;
            $output .= implode(PHP_EOL, $enabled) . PHP_EOL;
        }
        return $output;
    }
}
