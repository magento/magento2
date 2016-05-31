<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Setup\Module\DataSetupFactory;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Used to uninstall registry from the database and deployment config
 */
class ModuleRegistryUninstaller
{
    /**
     * @var DataSetupFactory
     */
    private $dataSetupFactory;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var DeploymentConfig\Writer
     */
    private $writer;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * Constructor
     *
     * @param DataSetupFactory $dataSetupFactory
     * @param DeploymentConfig $deploymentConfig
     * @param DeploymentConfig\Writer $writer
     * @param Loader $loader
     */
    public function __construct(
        DataSetupFactory $dataSetupFactory,
        DeploymentConfig $deploymentConfig,
        DeploymentConfig\Writer $writer,
        Loader $loader
    ) {
        $this->dataSetupFactory = $dataSetupFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->writer = $writer;
        $this->loader = $loader;
    }

    /**
     * Removes module from setup_module table
     *
     * @param OutputInterface $output
     * @param string[] $modules
     * @return void
     */
    public function removeModulesFromDb(OutputInterface $output, array $modules)
    {
        $output->writeln(
            '<info>Removing ' . implode(', ', $modules) . ' from module registry in database</info>'
        );
        /** @var \Magento\Framework\Setup\ModuleDataSetupInterface $setup */
        $setup = $this->dataSetupFactory->create();
        foreach ($modules as $module) {
            $setup->deleteTableRow('setup_module', 'module', $module);
        }
    }

    /**
     * Removes module from deployment configuration
     *
     * @param OutputInterface $output
     * @param string[] $modules
     * @return void
     */
    public function removeModulesFromDeploymentConfig(OutputInterface $output, array $modules)
    {
        $output->writeln(
            '<info>Removing ' . implode(', ', $modules) .  ' from module list in deployment configuration</info>'
        );
        $configuredModules = $this->deploymentConfig->getConfigData(
            \Magento\Framework\Config\ConfigOptionsListConstants::KEY_MODULES
        );
        $existingModules = $this->loader->load($modules);
        $newModules = [];
        foreach (array_keys($existingModules) as $module) {
            $newModules[$module] = isset($configuredModules[$module]) ? $configuredModules[$module] : 0;
        }
        $this->writer->saveConfig(
            [
                \Magento\Framework\Config\File\ConfigFilePool::APP_CONFIG =>
                    [\Magento\Framework\Config\ConfigOptionsListConstants::KEY_MODULES => $newModules]
            ],
            true
        );
    }
}
