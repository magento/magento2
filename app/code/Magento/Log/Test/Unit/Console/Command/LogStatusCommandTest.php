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
        $resource->expects($this->once())->method('getTablesInfo')->willReturn(
            [['name' => 'log_customer', 'rows' => '0', 'data_length' => '16.00Kb', 'index_length' => '16.00Kb']]
        );
        $objectManagerFactory->expects($this->once())->method('create')->willReturn($objectManager);
        $commandTester = new CommandTester(new LogStatusCommand($objectManagerFactory));
        $commandTester->execute([]);
        $expectedMsg = '-----------------------------------+------------+------------+------------+' . PHP_EOL
            . 'Table Name                         | Rows       | Data Size  | Index Size |' . PHP_EOL
            . '-----------------------------------+------------+------------+------------+' . PHP_EOL
            . 'log_customer                       | 0          | 16 b       | 16 b       |' . PHP_EOL
            . '-----------------------------------+------------+------------+------------+' . PHP_EOL
            . 'Total                              | 0          | 16 b       | 16 b       |' . PHP_EOL
            . '-----------------------------------+------------+------------+------------+';
        $this->assertEquals($expectedMsg . PHP_EOL, $commandTester->getDisplay());
    }
}
