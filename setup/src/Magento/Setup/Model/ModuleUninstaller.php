<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Setup\Module\Setup;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleUninstaller extends \Magento\Framework\Composer\AbstractComponentUninstaller
{
    const OPTION_REMOVE_DATA = 'data';
    const OPTION_REMOVE_CODE = 'code';
    const OPTION_REMOVE_REGISTRY = 'registry';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Writer
     */
    private $writer;

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader
     */
    private $loader;

    /**
     * @var \Magento\Framework\Module\PackageInfo
     */
    private $packageInfo;

    /**
     * @var \Magento\Framework\Composer\Remove
     */
    private $remove;

    /**
     * @var \Magento\Framework\Module\Resource
     */
    private $resource;

    /**
     * @var UninstallCollector
     */
    private $collector;

    /**
     * @var \Magento\Setup\Module\DataSetupFactory
     */
    private $dataSetupFactory;

    /**
     * @var \Magento\Setup\Module\SetupFactory
     */
    private $setupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\App\DeploymentConfig\Writer $writer
     * @param \Magento\Framework\Module\ModuleList\Loader $loader
     * @param ObjectManagerProvider $objectManagerProvider
     * @param \Magento\Framework\Composer\Remove $remove
     * @param UninstallCollector $collector
     * @param \Magento\Setup\Module\DataSetupFactory $dataSetupFactory
     * @param \Magento\Setup\Module\SetupFactory $setupFactory
     */
    public function __construct(
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\DeploymentConfig\Writer $writer,
        \Magento\Framework\Module\ModuleList\Loader $loader,
        ObjectManagerProvider $objectManagerProvider,
        \Magento\Framework\Composer\Remove $remove,
        UninstallCollector $collector,
        \Magento\Setup\Module\DataSetupFactory $dataSetupFactory,
        \Magento\Setup\Module\SetupFactory $setupFactory
    ) {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->deploymentConfig = $deploymentConfig;
        $this->writer = $writer;
        $this->loader = $loader;
        $this->remove = $remove;
        $this->collector = $collector;
        $this->dataSetupFactory = $dataSetupFactory;
        $this->setupFactory = $setupFactory;
    }

    /**
     * Uninstall the module depending on uninstall options
     *
     * @param OutputInterface $output
     * @param array $modules
     * @param array $options
     * @return void
     */
    public function uninstall(OutputInterface $output, array $modules, array $options)
    {
        $this->objectManager = $this->objectManagerProvider->get();
        if (isset($options[self::OPTION_REMOVE_DATA]) && $options[self::OPTION_REMOVE_DATA]) {
            $this->removeData($output, $modules);
        }
        if (isset($options[self::OPTION_REMOVE_CODE]) && $options[self::OPTION_REMOVE_CODE]) {
            $this->removeCode($output, $modules);
        }
        if (isset($options[self::OPTION_REMOVE_REGISTRY]) && $options[self::OPTION_REMOVE_REGISTRY]) {
            $this->removeModulesFromDb($output, $modules);
            $this->removeModulesFromDeploymentConfig($output, $modules);
        }
    }

    /**
     * Invoke remove data routine in each specified module
     *
     * @param OutputInterface $output
     * @param array $modules
     * @return void
     */
    private function removeData(OutputInterface $output, array $modules)
    {
        $uninstalls = $this->collector->collectUninstall();
        $setupModel = $this->setupFactory->create();
        $this->resource = $this->objectManager->get('Magento\Framework\Module\Resource');
        foreach ($modules as $module) {
            if (isset($uninstalls[$module])) {
                $output->writeln("<info>Removing data of $module</info>");
                $uninstalls[$module]->uninstall(
                    $setupModel,
                    new ModuleContext($this->resource->getDbVersion($module) ?: '')
                );
            } else {
                $output->writeln("<info>No data to clear in $module</info>");
            }
        }
    }

    /**
     * Run 'composer remove' to remove code
     *
     * @param OutputInterface $output
     * @param array $modules
     * @return void
     */
    private function removeCode(OutputInterface $output, array $modules)
    {
        $output->writeln('<info>Removing code from Magento codebase:</info>');
        $packages = [];
        $this->packageInfo = $this->objectManager->get('Magento\Framework\Module\PackageInfoFactory')->create();
        foreach ($modules as $module) {
            $packages[] = $this->packageInfo->getPackageName($module);
        }
        $this->remove->remove($packages);
    }

    /**
     * Removes module from setup_module table
     *
     * @param OutputInterface $output
     * @param string[] $modules
     * @return void
     */
    private function removeModulesFromDb(OutputInterface $output, array $modules)
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
    private function removeModulesFromDeploymentConfig(OutputInterface $output, array $modules)
    {
        $output->writeln(
            '<info>Removing ' . implode(', ', $modules) .  ' from module list in deployment configuration</info>'
        );
        $existingModules = $this->deploymentConfig->getConfigData(ConfigOptionsListConstants::KEY_MODULES);
        $newSort = $this->loader->load($modules);
        $newModules = [];
        foreach (array_keys($newSort) as $module) {
            $newModules[$module] = $existingModules[$module];
        }
        $this->writer->saveConfig(
            [
                \Magento\Framework\Config\File\ConfigFilePool::APP_CONFIG =>
                    [ConfigOptionsListConstants::KEY_MODULES => $newModules]
            ],
            true
        );
    }
}
