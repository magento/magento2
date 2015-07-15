<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Log\Console\Command\LogStatusCommand;
use Symfony\Component\Console\Tester\CommandTester;

class LogStatusCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $resourceFactory = $this->getMockBuilder('Magento\Log\Model\Resource\ShellFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resource = $this->getMock('Magento\Log\Model\Resource\Shell', [], [], '', false);
        $resourceFactory->expects($this->once())->method('create')->willReturn($resource);
        $resource->expects($this->once())->method('getTablesInfo')->willReturn(
            [['name' => 'log_customer', 'rows' => '1', 'data_length' => '16384', 'index_length' => '1024']]
        );
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $objectManager->expects($this->once())->method('create')->willReturn($resourceFactory);
        $configLoader = $this->getMock('Magento\Framework\App\ObjectManager\ConfigLoader', [], [], '', false);
        $state = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $configLoader->expects($this->once())
            ->method('load')
            ->with(FrontNameResolver::AREA_CODE)
            ->will($this->returnValue([]));
        $state->expects($this->once())
            ->method('setAreaCode')
            ->with(FrontNameResolver::AREA_CODE);
        $commandTester = new CommandTester(new LogStatusCommand($objectManager, $configLoader, $state));

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
