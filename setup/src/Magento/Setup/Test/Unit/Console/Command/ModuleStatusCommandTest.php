<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Console\Command\ModuleStatusCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ModuleStatusCommandTest extends TestCase
{
    public function testExecute()
    {
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($objectManager));
        $moduleList = $this->createMock(ModuleList::class);
        $fullModuleList = $this->createMock(FullModuleList::class);
        $objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                [ModuleList::class, [], $moduleList],
                [FullModuleList::class, [], $fullModuleList],
            ]));
        $moduleList->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Magento_Module1', 'Magento_Module2']));
        $fullModuleList->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Magento_Module1', 'Magento_Module2', 'Magento_Module3']));
        $commandTester = new CommandTester(new ModuleStatusCommand($objectManagerProvider));
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'List of enabled modules%aMagento_Module1%aMagento_Module2%a'
            . 'List of disabled modules%aMagento_Module3%a',
            $commandTester->getDisplay()
        );
    }
}
