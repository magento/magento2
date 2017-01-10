<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Console;

use Magento\Framework\Console\CommandList;
use Symfony\Component\Console\Command\Command;

class CommandListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Console\CommandList
     */
    private $commandList;

    /**
     * @var Symfony\Component\Console\Command\Command
     */
    private $testCommand;

    protected function setUp()
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
        $this->assertEquals(1, count($commands));
    }
}
