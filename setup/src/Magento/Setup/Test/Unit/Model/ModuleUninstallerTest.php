<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;

class ModuleUninstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\DeploymentConfig\Writer
     */
    private $writer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\ModuleList\Loader
     */
    private $loader;

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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Module\DataSetup
     */
    private $dataSetup;

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

    public function setUp()
    {
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->writer = $this->getMock('Magento\Framework\App\DeploymentConfig\Writer', [], [], '', false);
        $this->loader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);

        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],'',
            false
        );
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);

        $this->remove = $this->getMock('Magento\Framework\Composer\Remove', [], [], '', false);
        $this->collector = $this->getMock('Magento\Setup\Model\UninstallCollector', [], [], '', false);

        $this->dataSetup = $this->getMock('Magento\Setup\Module\DataSetup', [], [], '', false);
        $dataSetupFactory = $this->getMock('Magento\Setup\Module\DataSetupFactory', [], [], '', false);
        $dataSetupFactory->expects($this->any())->method('create')->willReturn($this->dataSetup);
        $this->setup = $this->getMock('Magento\Setup\Module\Setup', [], [], '', false);
        $setupFactory = $this->getMock('Magento\Setup\Module\SetupFactory', [], [], '', false);
        $setupFactory->expects($this->any())->method('create')->willReturn($this->setup);

        $this->uninstaller = new ModuleUninstaller(
            $this->deploymentConfig,
            $this->writer,
            $this->loader,
            $objectManagerProvider,
            $this->remove,
            $this->collector,
            $dataSetupFactory,
            $setupFactory
        );

        $this->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface', [], [], '', false);
    }

    public function testUninstallRemoveData()
    {
        $uninstall = $this->getMockForAbstractClass('Magento\Framework\Setup\UninstallInterface', [], '', false);
        $uninstall->expects($this->atLeastOnce())
            ->method('uninstall')
            ->with($this->setup, $this->isInstanceOf('Magento\Setup\Model\ModuleContext'));
        $this->collector->expects($this->once())
            ->method('collectUninstall')
            ->willReturn(['moduleA' => $uninstall, 'moduleB' => $uninstall]);

        $resource = $this->getMock('Magento\Framework\Module\Resource', [], [], '', false);
        $resource->expects($this->atLeastOnce())->method('getDbVersion')->willReturn('1.0');

        $this->output->expects($this->atLeastOnce())->method('writeln');

        $this->objectManager->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Module\Resource')
            ->willReturn($resource);
        $this->uninstaller->uninstall(
            $this->output,
            ['moduleA', 'moduleB'],
            [ModuleUninstaller::OPTION_REMOVE_DATA => true]
        );
    }

    public function testUninstallRemoveCode()
    {
        $this->output->expects($this->once())->method('writeln');
        $packageInfoFactory = $this->getMock('Magento\Framework\Module\PackageInfoFactory', [], [], '', false);
        $packageInfo = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $packageInfo->expects($this->atLeastOnce())->method('getPackageName');
        $packageInfoFactory->expects($this->once())->method('create')->willReturn($packageInfo);
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Module\PackageInfoFactory')
            ->willReturn($packageInfoFactory);
        $this->remove->expects($this->once())->method('remove');
        $this->uninstaller->uninstall(
            $this->output,
            ['moduleA', 'moduleB'],
            [ModuleUninstaller::OPTION_REMOVE_CODE => true]
        );
    }

    public function testUninstallRemoveRegistry()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
        $this->dataSetup->expects($this->atLeastOnce())->method('deleteTableRow');
        $this->deploymentConfig->expects($this->once())
            ->method('getConfigData')
            ->willReturn(['moduleA' => 1, 'moduleB' => 1, 'moduleC' => 1, 'moduleD' => 1]);
        $this->loader->expects($this->once())->method('load')->willReturn(['moduleC' => [], 'moduleD' => []]);
        $this->writer->expects($this->once())
            ->method('saveConfig')
            ->with(
                [
                    ConfigFilePool::APP_CONFIG => [
                        ConfigOptionsListConstants::KEY_MODULES => ['moduleC' => 1, 'moduleD' => 1]
                    ]
                ]
            );
        $this->uninstaller->uninstall(
            $this->output,
            ['moduleA', 'moduleB'],
            [ModuleUninstaller::OPTION_REMOVE_REGISTRY => true]
        );
    }
}
