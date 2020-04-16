<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\ModuleUninstaller;
use Magento\Framework\Setup\Patch\PatchApplier;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModuleUninstallerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Composer\Remove
     */
    private $remove;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Model\UninstallCollector
     */
    private $collector;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Module\Setup
     */
    private $setup;

    /**
     * @var ModuleUninstaller
     */
    private $uninstaller;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Model\ModuleRegistryUninstaller
     */
    private $moduleRegistryUninstaller;

    /**
     * @var PatchApplier|\PHPUnit\Framework\MockObject\MockObject
     */
    private $patchApplierMock;

    protected function setUp(): void
    {
        $this->moduleRegistryUninstaller = $this->createMock(\Magento\Setup\Model\ModuleRegistryUninstaller::class);
        $this->objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);

        $this->remove = $this->createMock(\Magento\Framework\Composer\Remove::class);
        $this->collector = $this->createMock(\Magento\Setup\Model\UninstallCollector::class);

        $this->setup = $this->createMock(\Magento\Setup\Module\Setup::class);
        $this->patchApplierMock = $this->createMock(PatchApplier::class);
        $setupFactory = $this->createMock(\Magento\Setup\Module\SetupFactory::class);
        $setupFactory->expects($this->any())->method('create')->willReturn($this->setup);

        $this->uninstaller = new ModuleUninstaller(
            $objectManagerProvider,
            $this->remove,
            $this->collector,
            $setupFactory,
            $this->moduleRegistryUninstaller
        );

        $this->output = $this->createMock(\Symfony\Component\Console\Output\OutputInterface::class);
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

        $resource = $this->createMock(\Magento\Framework\Module\ModuleResource::class);
        $resource->expects($this->atLeastOnce())->method('getDbVersion')->willReturn('1.0');

        $this->output->expects($this->atLeastOnce())->method('writeln');

        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [\Magento\Framework\Module\ModuleResource::class, $resource],
                    [PatchApplier::class, $this->patchApplierMock]
                ]
            );
        $this->patchApplierMock->expects($this->exactly(2))->method('revertDataPatches')->willReturnMap(
            [
                ['moduleA'],
                ['moduleB']
            ]
        );
        $this->uninstaller->uninstallData($this->output, ['moduleA', 'moduleB']);
    }

    public function testUninstallRemoveCode()
    {
        $this->moduleRegistryUninstaller->expects($this->never())->method($this->anything());
        $this->output->expects($this->once())->method('writeln');
        $packageInfoFactory = $this->createMock(\Magento\Framework\Module\PackageInfoFactory::class);
        $packageInfo = $this->createMock(\Magento\Framework\Module\PackageInfo::class);
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
