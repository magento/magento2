<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron\Helper;

use Magento\Setup\Model\Cron\Helper\ModuleUninstall;

class ModuleUninstallTest extends \PHPUnit_Framework_TestCase
{
    public function testUninstallRemoveData()
    {
        $output =
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $packageInfoFactory = $this->getMock(\Magento\Framework\Module\PackageInfoFactory::class, [], [], '', false);
        $packageInfo = $this->getMock(\Magento\Framework\Module\PackageInfo::class, [], [], '', false);
        $packageInfo->expects($this->once())->method('getModuleName')->willReturn('Module_A');
        $packageInfoFactory->expects($this->any())->method('create')->willReturn($packageInfo);
        $moduleUninstaller = $this->getMock(\Magento\Setup\Model\ModuleUninstaller::class, [], [], '', false);
        $moduleUninstaller->expects($this->once())->method('uninstallData')->with($output, ['Module_A']);
        $moduleRegistryUninstaller =
            $this->getMock(\Magento\Setup\Model\ModuleRegistryUninstaller::class, [], [], '', false);
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
            $this->getMockForAbstractClass(\Symfony\Component\Console\Output\OutputInterface::class, [], '', false);
        $packageInfoFactory = $this->getMock(\Magento\Framework\Module\PackageInfoFactory::class, [], [], '', false);
        $packageInfo = $this->getMock(\Magento\Framework\Module\PackageInfo::class, [], [], '', false);
        $packageInfo->expects($this->once())->method('getModuleName')->willReturn('Module_A');
        $packageInfoFactory->expects($this->any())->method('create')->willReturn($packageInfo);
        $moduleUninstaller = $this->getMock(\Magento\Setup\Model\ModuleUninstaller::class, [], [], '', false);
        $moduleUninstaller->expects($this->never())->method('uninstallData');
        $moduleRegistryUninstaller =
            $this->getMock(\Magento\Setup\Model\ModuleRegistryUninstaller::class, [], [], '', false);
        $moduleRegistryUninstaller->expects($this->once())->method('removeModulesFromDb')->with($output, ['Module_A']);
        $moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($output, ['Module_A']);

        $moduleUninstall = new ModuleUninstall($moduleUninstaller, $moduleRegistryUninstaller, $packageInfoFactory);
        $moduleUninstall->uninstall($output, 'vendor/module-package', false);
    }
}
