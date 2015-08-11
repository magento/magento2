<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class to uninstall a module component
 */
class ModuleUninstaller extends \Magento\Framework\Composer\AbstractComponentUninstaller
{
    /**#@+
     * Module uninstall options
     */
    const OPTION_UNINSTALL_DATA = 'data';
    const OPTION_UNINSTALL_CODE = 'code';
    const OPTION_UNINSTALL_REGISTRY = 'registry';
    /**#@-*/

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Composer\Remove
     */
    private $remove;

    /**
     * @var UninstallCollector
     */
    private $collector;

    /**
     * @var \Magento\Setup\Module\SetupFactory
     */
    private $setupFactory;

    /**
     * @var ModuleRegistryUninstaller
     */
    private $moduleRegistryUninstaller;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param \Magento\Framework\Composer\Remove $remove
     * @param UninstallCollector $collector
     * @param \Magento\Setup\Module\SetupFactory $setupFactory
     * @param ModuleRegistryUninstaller $moduleRegistryUninstaller
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        \Magento\Framework\Composer\Remove $remove,
        UninstallCollector $collector,
        \Magento\Setup\Module\SetupFactory $setupFactory,
        ModuleRegistryUninstaller $moduleRegistryUninstaller
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->remove = $remove;
        $this->collector = $collector;
        $this->setupFactory = $setupFactory;
        $this->moduleRegistryUninstaller = $moduleRegistryUninstaller;
    }

    /**
     * Uninstall the module depending on uninstall options
     *
     * @param OutputInterface $output
     * @param array $modules Module names
     * @param array $options
     * @return void
     */
    public function uninstall(OutputInterface $output, array $modules, array $options)
    {
        if (isset($options[self::OPTION_UNINSTALL_DATA]) && $options[self::OPTION_UNINSTALL_DATA]) {
            $this->removeData($output, $modules);
        }
        if (isset($options[self::OPTION_UNINSTALL_CODE]) && $options[self::OPTION_UNINSTALL_CODE]) {
            $this->removeCode($output, $modules);
        }
        if (isset($options[self::OPTION_UNINSTALL_REGISTRY]) && $options[self::OPTION_UNINSTALL_REGISTRY]) {
            $this->moduleRegistryUninstaller->removeModulesFromDb($output, $modules);
            $this->moduleRegistryUninstaller->removeModulesFromDeploymentConfig($output, $modules);
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
        $resource = $this->objectManager->get('Magento\Framework\Module\Resource');
        foreach ($modules as $module) {
            if (isset($uninstalls[$module])) {
                $output->writeln("<info>Removing data of $module</info>");
                $uninstalls[$module]->uninstall(
                    $setupModel,
                    new ModuleContext($resource->getDbVersion($module) ?: '')
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
        /** @var \Magento\Framework\Module\PackageInfo $packageInfo */
        $packageInfo = $this->objectManager->get('Magento\Framework\Module\PackageInfoFactory')->create();
        foreach ($modules as $module) {
            $packages[] = $packageInfo->getPackageName($module);
        }
        $this->remove->remove($packages);
    }
}
