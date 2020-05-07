<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Setup\Model\ModuleRegistryUninstaller;
use Magento\Setup\Module\DataSetup;
use Magento\Setup\Module\DataSetupFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleRegistryUninstallerTest extends TestCase
{
    /**
     * @var MockObject|DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var MockObject|Writer
     */
    private $writer;

    /**
     * @var MockObject|Loader
     */
    private $loader;

    /**
     * @var MockObject|DataSetup
     */
    private $dataSetup;

    /**
     * @var MockObject|OutputInterface
     */
    private $output;

    /**
     * @var ModuleRegistryUninstaller
     */
    private $moduleRegistryUninstaller;

    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->writer = $this->createMock(Writer::class);
        $this->loader = $this->createMock(Loader::class);
        $this->dataSetup = $this->createMock(DataSetup::class);
        $dataSetupFactory = $this->createMock(DataSetupFactory::class);
        $dataSetupFactory->expects($this->any())->method('create')->willReturn($this->dataSetup);
        $this->output = $this->getMockForAbstractClass(OutputInterface::class);
        $this->moduleRegistryUninstaller = new ModuleRegistryUninstaller(
            $dataSetupFactory,
            $this->deploymentConfig,
            $this->writer,
            $this->loader
        );
    }

    public function testRemoveModulesFromDb()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
        $this->dataSetup->expects($this->atLeastOnce())->method('deleteTableRow');
        $this->moduleRegistryUninstaller->removeModulesFromDb($this->output, ['moduleA', 'moduleB']);
    }

    public function testRemoveModulesFromDeploymentConfig()
    {
        $this->output->expects($this->atLeastOnce())->method('writeln');
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
        $this->moduleRegistryUninstaller->removeModulesFromDeploymentConfig($this->output, ['moduleA', 'moduleB']);
    }
}
