<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Test\Unit\Console\Command;

use Magento\Log\Console\Command\LogStatusCommand;
use Symfony\Component\Console\Tester\CommandTester;

class LogStatusCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $objectManagerFactory = $this->getMock('Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $objectManager = $this->getMock('Magento\Framework\App\ObjectManager', [], [], '', false);
        $resourceFactory = $this->getMock('Magento\Log\Model\Resource\ShellFactory', [], [], '', false);
        $objectManager->expects($this->once())->method('create')->willReturn($resourceFactory);
        $resource = $this->getMock('Magento\Log\Model\Resource\Shell', [], [], '', false);
        $resourceFactory->expects($this->once())->method('create')->willReturn($resource);
        $resource->expects($this->once())->method('getTablesInfo')->willReturn([]);
        $objectManagerFactory->expects($this->once())->method('create')->willReturn($objectManager);
        $commandTester = new CommandTester(new LogStatusCommand($objectManagerFactory));
        $commandTester->execute([]);
        $this->assertStringStartsWith('-----------------------------------+------------+------------+------------+'
            . PHP_EOL, $commandTester->getDisplay());
    }
}
