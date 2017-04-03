<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ModuleUninstaller;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModuleUninstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Composer\Remove
     */
    private $remove;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\UninstallCollector
     */
    private $collector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Module\Setup
     */
    private $setup;

    /**
     * @var ModuleUninstaller
     */
    private $uninstaller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ModuleRegistryUninstaller
     */
    private $moduleRegistryUninstaller;

    public function setUp()
    {
        $this->moduleRegistryUninstaller = $this->getMock(
            \Magento\Setup\Model\ModuleRegistryUninstaller::class,
            [],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);

        $this->remove = $this->getMock(\Magento\Framework\Composer\Remove::class, [], [], '', false);
        $this->collector = $this->getMock(\Magento\Setup\Model\UninstallCollector::class, [], [], '', false);

        $this->setup = $this->getMock(\Magento\Setup\Module\Setup::class, [], [], '', false);
        $setupFactory = $this->getMock(\Magento\Setup\Module\SetupFactory::class, [], [], '', false);
        $setupFactory->expects($this->any())->method('create')->willReturn($this->setup);

        $this->uninstaller = new ModuleUninstaller(
            $objectManagerProvider,
            $this->remove,
            $this->collector,
            $setupFactory,
            $this->moduleRegistryUninstaller
        );

        $this->output = $this->getMock(\Symfony\Component\Console\Output\OutputInterface::class, [], [], '', false);
    }

    public function testUninstallRemoveData()
    {
        $this->moduleRegistryUninstaller->expects($this->never())->method($this->anything());
        $uninstall = $this->getMockForAbstractClass(\Magento\Framework\Setup\UninstallInterface::class, [], '', false);
        $uninstall->expects($this->atLeastOnce())
            ->method('uninstall')
            ->with($this->setup, $this->isInstanceOf(\Magento\Setup\Model\ModuleContext::class));
        $this->collector->expects($this->once())
            ->method('collectUninstall')
            ->willReturn(['moduleA' => $uninstall, 'moduleB' => $uninstall]);

        $resource = $this->getMock(\Magento\Framework\Module\ModuleResource::class, [], [], '', false);
        $resource->expects($this->atLeastOnce())->method('getDbVersion')->willReturn('1.0');

        $this->output->expects($this->atLeastOnce())->method('writeln');

        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Module\ModuleResource::class)
            ->willReturn($resource);
        $this->uninstaller->uninstallData($this->output, ['moduleA', 'moduleB']);
    }

    public function testUninstallRemoveCode()
    {
        $this->moduleRegistryUninstaller->expects($this->never())->method($this->anything());
        $this->output->expects($this->once())->method('writeln');
        $packageInfoFactory = $this->getMock(\Magento\Framework\Module\PackageInfoFactory::class, [], [], '', false);
        $packageInfo = $this->getMock(\Magento\Framework\Module\PackageInfo::class, [], [], '', false);
        $packageInfo->expects($this->atLeastOnce())->method('getPackageName');
        $packageInfoFactory->expects($this->once())->method('create')->willReturn($packageInfo);
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Module\PackageInfoFactory::class)
            ->willReturn($packageInfoFactory);
        $this->remove->expects($this->once())->method('remove');
        $this->uninstaller->uninstallCode($this->output, ['moduleA', 'moduleB']);
    }
}
