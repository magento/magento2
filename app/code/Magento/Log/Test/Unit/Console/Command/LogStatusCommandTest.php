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
        $resourceFactory = $this->getMockBuilder('Magento\Log\Model\Resource\ShellFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $objectManager->expects($this->once())->method('create')->willReturn($resourceFactory);
        $resource = $this->getMock('Magento\Log\Model\Resource\Shell', [], [], '', false);
        $resourceFactory->expects($this->once())->method('create')->willReturn($resource);
        $resource->expects($this->once())->method('getTablesInfo')->willReturn(
            [['name' => 'log_customer', 'rows' => '1', 'data_length' => '16384', 'index_length' => '1024']]
        );
        $objectManagerFactory->expects($this->once())->method('create')->willReturn($objectManager);
        $commandTester = new CommandTester(new LogStatusCommand($objectManagerFactory));
        $commandTester->execute([]);
        $expectedMsg = '-----------------------------------+------------+------------+------------+' . PHP_EOL
            . 'Table Name                         | Rows       | Data Size  | Index Size |' . PHP_EOL
            . '-----------------------------------+------------+------------+------------+' . PHP_EOL
            . 'log_customer                       | 1          | 16.00Kb    | 1.00Kb     |' . PHP_EOL
            . '-----------------------------------+------------+------------+------------+' . PHP_EOL
            . 'Total                              | 1          | 16.00Kb    | 1.00Kb     |' . PHP_EOL
            . '-----------------------------------+------------+------------+------------+%w';
        $this->assertStringMatchesFormat($expectedMsg, $commandTester->getDisplay());
    }
}
