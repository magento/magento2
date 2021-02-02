<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\ModuleStatusCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ModuleStatusCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->willReturn($objectManager);
        $moduleList = $this->createMock(\Magento\Framework\Module\ModuleList::class);
        $fullModuleList = $this->createMock(\Magento\Framework\Module\FullModuleList::class);
        $objectManager->expects($this->any())
            ->method('create')
            ->willReturnMap([
                [\Magento\Framework\Module\ModuleList::class, [], $moduleList],
                [\Magento\Framework\Module\FullModuleList::class, [], $fullModuleList],
            ]);
        $moduleList->expects($this->any())
            ->method('getNames')
            ->willReturn(['Magento_Module1', 'Magento_Module2']);
        $fullModuleList->expects($this->any())
            ->method('getNames')
            ->willReturn(['Magento_Module1', 'Magento_Module2', 'Magento_Module3']);
        $commandTester = new CommandTester(new ModuleStatusCommand($objectManagerProvider));
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'List of enabled modules%aMagento_Module1%aMagento_Module2%a'
            . 'List of disabled modules%aMagento_Module3%a',
            $commandTester->getDisplay()
        );
    }
}
