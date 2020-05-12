<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron\Helper;

use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Setup\Model\Cron\Helper\ModuleUninstall;
use Magento\Setup\Model\ModuleRegistryUninstaller;
use Magento\Setup\Model\ModuleUninstaller;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleUninstallTest extends TestCase
{
    public function testUninstallRemoveData()
    {
        $output =
            $this->getMockForAbstractClass(OutputInterface::class, [], '', false);
        $packageInfoFactory = $this->createMock(PackageInfoFactory::class);
        $packageInfo = $this->createMock(PackageInfo::class);
        $packageInfo->expects($this->once())->method('getModuleName')->willReturn('Module_A');
        $packageInfoFactory->expects($this->any())->method('create')->willReturn($packageInfo);
        $moduleUninstaller = $this->createMock(ModuleUninstaller::class);
        $moduleUninstaller->expects($this->once())->method('uninstallData')->with($output, ['Module_A']);
        $moduleRegistryUninstaller =
            $this->createMock(ModuleRegistryUninstaller::class);
        $moduleRegistryUninstaller->expects($this->once())->method('removeModulesFromDb')->with($output, ['Module_A']);
        $moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($output, ['Module_A']);

        $moduleUninstall = new ModuleUninstall($moduleUninstaller, $moduleRegistryUninstaller, $packageInfoFactory);
        $moduleUninstall->uninstall($output, 'vendor/module-package', true);
    }

    public function testUninstallNotRemoveData()
    {
        $output =
            $this->getMockForAbstractClass(OutputInterface::class, [], '', false);
        $packageInfoFactory = $this->createMock(PackageInfoFactory::class);
        $packageInfo = $this->createMock(PackageInfo::class);
        $packageInfo->expects($this->once())->method('getModuleName')->willReturn('Module_A');
        $packageInfoFactory->expects($this->any())->method('create')->willReturn($packageInfo);
        $moduleUninstaller = $this->createMock(ModuleUninstaller::class);
        $moduleUninstaller->expects($this->never())->method('uninstallData');
        $moduleRegistryUninstaller =
            $this->createMock(ModuleRegistryUninstaller::class);
        $moduleRegistryUninstaller->expects($this->once())->method('removeModulesFromDb')->with($output, ['Module_A']);
        $moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($output, ['Module_A']);

        $moduleUninstall = new ModuleUninstall($moduleUninstaller, $moduleRegistryUninstaller, $packageInfoFactory);
        $moduleUninstall->uninstall($output, 'vendor/module-package', false);
    }
}
