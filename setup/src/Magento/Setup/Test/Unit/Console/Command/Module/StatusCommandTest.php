<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command\Module;

use Magento\Setup\Console\Command\Module\StatusCommand;
use Symfony\Component\Console\Tester\CommandTester;

class StatusCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($objectManager));
        $moduleList = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $fullModuleList = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Framework\Module\ModuleList', [], $moduleList],
                ['Magento\Framework\Module\FullModuleList', [], $fullModuleList],
            ]));
        $moduleList->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Magento_Module1', 'Magento_Module2']));
        $fullModuleList->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Magento_Module1', 'Magento_Module2', 'Magento_Module3']));
        $commandTester = new CommandTester(new StatusCommand($objectManagerProvider));
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'List of enabled modules%aMagento_Module1%aMagento_Module2%a'
            . 'List of disabled modules%aMagento_Module3%a',
            $commandTester->getDisplay()
        );
    }
}
