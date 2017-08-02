<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron\Helper;

use Magento\Framework\Module\PackageInfoFactory;
use Magento\Setup\Model\ModuleRegistryUninstaller;
use Magento\Setup\Model\ModuleUninstaller;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class for JobComponentUninstall to uninstall a module component
 * @since 2.0.0
 */
class ModuleUninstall
{
    /**
     * @var ModuleUninstaller
     * @since 2.0.0
     */
    private $moduleUninstaller;

    /**
     * @var ModuleRegistryUninstaller
     * @since 2.0.0
     */
    private $moduleRegistryUninstaller;

    /**
     * @var PackageInfoFactory
     * @since 2.0.0
     */
    private $packageInfoFactory;

    /**
     * Constructor
     *
     * @param ModuleUninstaller $moduleUninstaller
     * @param ModuleRegistryUninstaller $moduleRegistryUninstaller
     * @param PackageInfoFactory $packageInfoFactory
     * @since 2.0.0
     */
    public function __construct(
        ModuleUninstaller $moduleUninstaller,
        ModuleRegistryUninstaller $moduleRegistryUninstaller,
        PackageInfoFactory $packageInfoFactory
    ) {
        $this->moduleUninstaller = $moduleUninstaller;
        $this->moduleRegistryUninstaller = $moduleRegistryUninstaller;
        $this->packageInfoFactory = $packageInfoFactory;
    }

    /**
     * Perform setup side uninstall
     *
     * @param OutputInterface $output
     * @param string $componentName
     * @param bool $dataOption
     * @return void
     * @since 2.0.0
     */
    public function uninstall(OutputInterface $output, $componentName, $dataOption)
    {
        $packageInfo = $this->packageInfoFactory->create();
        // convert to module name
        $moduleName = $packageInfo->getModuleName($componentName);
        if ($dataOption) {
            $this->moduleUninstaller->uninstallData($output, [$moduleName]);
        }
        $this->moduleRegistryUninstaller->removeModulesFromDb($output, [$moduleName]);
        $this->moduleRegistryUninstaller->removeModulesFromDeploymentConfig($output, [$moduleName]);
    }
}
