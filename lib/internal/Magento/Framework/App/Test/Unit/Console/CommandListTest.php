<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Console;

use Magento\Framework\Console\CommandList;
use Symfony\Component\Console\Command\Command;

/**
 * Test for
 *
 * @see Magento\Framework\Console\CommandList
 */
class CommandListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Console\CommandList
     */
    private $commandList;

    /**
     * @var Command
     */
    private $testCommand;

    protected function setUp(): void
    {
        $this->testCommand = new Command('Test');
        $commands = [
            $this->testCommand
        ];

        $this->commandList = new CommandList($commands);
    }

    public function testGetCommands()
    {
        $commands = $this->commandList->getCommands();
        $this->assertSame([$this->testCommand], $commands);
        $this->assertCount(1, $commands);
    }
}
