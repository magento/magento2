<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Console;

use Magento\Framework\Console\CommandList;

class CommandListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Console\CommandList
     */
    private $commandList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $commands =[
            'Symfony\Component\Console\Command\Command'
        ];

        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $this->commandList = new CommandList($this->objectManager, $commands);
    }

    public function testGetCommands()
    {
        $this->objectManager->expects($this->once())->method('get')->with('Symfony\Component\Console\Command\Command');
        $this->commandList->getCommands();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Class Symfony\Component\Console\Command\WrongCommand does not exist
     */
    public function testGetCommandsException()
    {
        $wrongCommands =[
            'Symfony\Component\Console\Command\WrongCommand'
        ];
        $commandList = new CommandList($this->objectManager, $wrongCommands);
        $commandList->getCommands();
    }
}
