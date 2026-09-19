<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Console\Command\ModuleStatusCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ModuleStatusCommandTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    public function testExecute()
    {
        $commandTester = $this->createCommandTester(
            ['Magento_Module1' => 1, 'Magento_Module2' => 1, 'Magento_Module3' => 0],
            []
        );
        $commandTester->execute([]);

        $this->assertStringMatchesFormat(
            'List of enabled modules%aMagento_Module1%aMagento_Module2%a'
            . 'List of disabled modules%aMagento_Module3%a',
            $commandTester->getDisplay()
        );
        $this->assertStringNotContainsString('overridden', $commandTester->getDisplay());
    }

    public function testExecuteShowsModulesOverriddenInEnvConfig()
    {
        $commandTester = $this->createCommandTester(
            ['Magento_Module1' => 1, 'Magento_Module2' => 1, 'Magento_Module3' => 1],
            ['Magento_Module3' => 0]
        );
        $commandTester->execute([]);

        $this->assertStringContainsString(
            'Modules overridden in app/etc/env.php:'
            . PHP_EOL
            . 'Magento_Module3 : disabled in env.php, enabled in app/etc/config.php',
            $commandTester->getDisplay()
        );
    }

    public function testExecuteIgnoresEnvConfigEntryMatchingAppConfig()
    {
        $commandTester = $this->createCommandTester(
            ['Magento_Module1' => 1, 'Magento_Module2' => 1, 'Magento_Module3' => 0],
            ['Magento_Module1' => 1, 'Magento_Module3' => 0]
        );
        $commandTester->execute([]);

        $this->assertStringNotContainsString('overridden', $commandTester->getDisplay());
    }

    public function testExecuteIgnoresEnvConfigEntryForUnknownModule()
    {
        $commandTester = $this->createCommandTester(
            ['Magento_Module1' => 1, 'Magento_Module2' => 1, 'Magento_Module3' => 0],
            ['Magento_Removed' => 1]
        );
        $commandTester->execute([]);

        $this->assertStringNotContainsString('overridden', $commandTester->getDisplay());
    }

    public function testExecuteAnnotatesSpecificOverriddenModule()
    {
        $commandTester = $this->createCommandTester(
            ['Magento_Module1' => 1, 'Magento_Module2' => 1, 'Magento_Module3' => 1],
            ['Magento_Module3' => 0]
        );
        $commandTester->execute(['module-names' => ['Magento_Module2', 'Magento_Module3']]);

        $this->assertStringMatchesFormat(
            'Magento_Module2 : Module is enabled%a'
            . 'Magento_Module3 :  Module is disabled'
            . ' (overridden in app/etc/env.php; app/etc/config.php: enabled)%a',
            $commandTester->getDisplay()
        );
    }

    public function testExecuteWithDisabledOptionDoesNotReadDeploymentConfig()
    {
        $commandTester = $this->createCommandTester(
            ['Magento_Module1' => 1, 'Magento_Module2' => 1, 'Magento_Module3' => 0],
            ['Magento_Module1' => 0]
        );
        $this->objectManager->expects($this->never())->method('get');
        $commandTester->execute(['--disabled' => true]);

        $this->assertStringMatchesFormat(
            'Magento_Module1%aMagento_Module3%a',
            $commandTester->getDisplay()
        );
        $this->assertStringNotContainsString('overridden', $commandTester->getDisplay());
    }

    /**
     * @param array $appConfigModules
     * @param array $envConfigModules
     * @return CommandTester
     */
    private function createCommandTester(array $appConfigModules, array $envConfigModules): CommandTester
    {
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManagerProvider->expects($this->any())
            ->method('get')
            ->willReturn($this->objectManager);

        $moduleList = $this->createMock(ModuleList::class);
        $fullModuleList = $this->createMock(FullModuleList::class);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->willReturnMap([
                [ModuleList::class, [], $moduleList],
                [FullModuleList::class, [], $fullModuleList],
            ]);

        $enabledModules = array_keys(array_filter(array_merge($appConfigModules, $envConfigModules)));
        $moduleList->expects($this->any())
            ->method('getNames')
            ->willReturn(array_values(array_intersect(array_keys($appConfigModules), $enabledModules)));
        $fullModuleList->expects($this->any())
            ->method('getNames')
            ->willReturn(array_keys($appConfigModules));

        $reader = $this->createMock(Reader::class);
        $reader->expects($this->any())
            ->method('load')
            ->willReturnCallback(function ($fileKey = null) use ($appConfigModules, $envConfigModules) {
                return $fileKey === ConfigFilePool::APP_CONFIG
                    ? ['modules' => $appConfigModules]
                    : ['modules' => $envConfigModules];
            });
        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([[Reader::class, $reader]]);

        return new CommandTester(new ModuleStatusCommand($objectManagerProvider));
    }
}
