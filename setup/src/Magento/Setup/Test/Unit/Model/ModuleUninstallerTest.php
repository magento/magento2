<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Composer\Remove;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Patch\PatchApplier;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Setup\Model\ModuleContext;
use Magento\Setup\Model\ModuleUninstaller;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\UninstallCollector;
use Magento\Setup\Module\Setup;
use Magento\Setup\Module\SetupFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModuleUninstallerTest extends TestCase
{
    /**
     * @var MockObject|ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MockObject|Remove
     */
    private $remove;

    /**
     * @var MockObject|UninstallCollector
     */
    private $collector;

    /**
     * @var MockObject|Setup
     */
    private $setup;

    /**
     * @var ModuleUninstaller
     */
    private $uninstaller;

    /**
     * @var MockObject|OutputInterface
     */
    private $output;

    /**
     * @var PatchApplier|MockObject
     */
    private $patchApplierMock;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);

        $this->remove = $this->createMock(Remove::class);
        $this->collector = $this->createMock(UninstallCollector::class);

        $this->setup = $this->createMock(Setup::class);
        $this->patchApplierMock = $this->createMock(PatchApplier::class);
        $setupFactory = $this->createMock(SetupFactory::class);
        $setupFactory->expects($this->any())->method('create')->willReturn($this->setup);

        $this->uninstaller = new ModuleUninstaller(
            $objectManagerProvider,
            $this->remove,
            $this->collector,
            $setupFactory
        );

        $this->output = $this->getMockForAbstractClass(OutputInterface::class);
    }

    public function testUninstallRemoveData()
    {
        $uninstall = $this->getMockForAbstractClass(UninstallInterface::class, [], '', false);
        $uninstall->expects($this->atLeastOnce())
            ->method('uninstall')
            ->with($this->setup, $this->isInstanceOf(ModuleContext::class));
        $this->collector->expects($this->once())
            ->method('collectUninstall')
            ->willReturn(['moduleA' => $uninstall, 'moduleB' => $uninstall]);

        $resource = $this->createMock(ModuleResource::class);
        $resource->expects($this->atLeastOnce())->method('getDbVersion')->willReturn('1.0');

        $this->output->expects($this->atLeastOnce())->method('writeln');

        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [ModuleResource::class, $resource],
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
        $this->output->expects($this->once())->method('writeln');
        $packageInfoFactory = $this->createMock(PackageInfoFactory::class);
        $packageInfo = $this->createMock(PackageInfo::class);
        $packageInfo->expects($this->atLeastOnce())->method('getPackageName');
        $packageInfoFactory->expects($this->once())->method('create')->willReturn($packageInfo);
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(PackageInfoFactory::class)
            ->willReturn($packageInfoFactory);
        $this->remove->expects($this->once())->method('remove');
        $this->uninstaller->uninstallCode($this->output, ['moduleA', 'moduleB']);
    }
}
