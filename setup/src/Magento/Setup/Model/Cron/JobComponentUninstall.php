<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\Updater;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Job to remove a component. Run by Setup Cron Task
 */
class JobComponentUninstall extends AbstractJob
{
    /**
     * Component name
     */
    const COMPONENT_NAME = 'name';

    /**
     * Data option
     */
    const DATA_OPTION = 'dataOption';

    /**#@+
     * Component types
     */
    const COMPONENT_MODULE = 'magento2-module';
    const COMPONENT_THEME = 'magento2-theme';
    const COMPONENT_LANGUAGE = 'magento2-language';
    /**#@-*/

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Updater
     */
    private $updater;

    /**
     * @var \Magento\Setup\Model\ModuleUninstaller
     */
    private $moduleUninstaller;

    /**
     * @var \Magento\Setup\Model\ModuleRegistryUninstaller
     */
    private $moduleRegistryUninstaller;

    /**
     * @var \Magento\Theme\Model\Theme\ThemeUninstaller
     */
    private $themeUninstaller;

    /**
     * @var \Magento\Theme\Model\Theme\ThemePackageInfo
     */
    private $themePackageInfo;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Composer\ComposerInformation $composerInformation
     * @param \Magento\Setup\Model\ModuleUninstaller $moduleUninstaller
     * @param \Magento\Setup\Model\ModuleRegistryUninstaller $moduleRegistryUninstaller
     * @param \Magento\Theme\Model\Theme\ThemeUninstaller $themeUninstaller
     * @param \Magento\Theme\Model\Theme\ThemePackageInfo $themePackageInfo
     * @param ObjectManagerProvider $objectManagerProvider
     * @param OutputInterface $output
     * @param Status $status
     * @param Updater $updater
     * @param $name
     * @param array $params
     */
    public function __construct(
        \Magento\Framework\Composer\ComposerInformation $composerInformation,
        \Magento\Setup\Model\ModuleUninstaller $moduleUninstaller,
        \Magento\Setup\Model\ModuleRegistryUninstaller $moduleRegistryUninstaller,
        \Magento\Theme\Model\Theme\ThemeUninstaller $themeUninstaller,
        \Magento\Theme\Model\Theme\ThemePackageInfo $themePackageInfo,
        ObjectManagerProvider $objectManagerProvider,
        OutputInterface $output,
        Status $status,
        Updater $updater,
        $name,
        $params = []
    ) {
        $this->composerInformation = $composerInformation;
        $this->moduleUninstaller = $moduleUninstaller;
        $this->moduleRegistryUninstaller = $moduleRegistryUninstaller;
        $this->themeUninstaller = $themeUninstaller;
        $this->objectManager = $objectManagerProvider->get();
        $this->updater = $updater;
        $this->themePackageInfo = $themePackageInfo;
        parent::__construct($output, $status, $name, $params);
    }

    /**
     * Run remove component job
     *
     * @return void
     * @throw \RuntimeException
     */
    public function execute()
    {
        if (!isset($this->params['components']) || !is_array($this->params['components'])) {
            throw new \RunTimeException('Job parameter format is incorrect');
        }
        $components = $this->params['components'];
        foreach ($components as $component) {
            $this->executeComponent($component);
        }
        $this->cleanUp();
        $errorMessage = $this->updater->createUpdaterTask($components, Updater::TASK_TYPE_UNINSTALL);
        if ($errorMessage) {
            throw new \RuntimeException($errorMessage);
        }
    }

    /**
     * Execute uninstall on a component
     *
     * @param array $component
     * @return void
     */
    private function executeComponent(array $component)
    {
        if (!isset($component[self::COMPONENT_NAME])) {
            throw new \RuntimeException('Job parameter format is incorrect');
        }

        $componentName = $component[self::COMPONENT_NAME];

        $type = $this->composerInformation->getInstalledMagentoPackages()[$componentName]['type'];

        if (!in_array($type, [self::COMPONENT_MODULE, self::COMPONENT_THEME, self::COMPONENT_LANGUAGE])) {
            throw new \RuntimeException('Unknown component type');
        }

        switch ($type) {
            case self::COMPONENT_MODULE:
                // convert to module name
                /** @var \Magento\Framework\Module\PackageInfo $packageInfo */
                $packageInfo = $this->objectManager->get('Magento\Framework\Module\PackageInfoFactory')->create();
                $moduleName = $packageInfo->getModuleName($componentName);
                if (isset($this->params[self::DATA_OPTION]) && $this->params[self::DATA_OPTION]) {
                    $this->moduleUninstaller->uninstallData($this->output, [$moduleName]);
                }
                $this->moduleRegistryUninstaller->removeModulesFromDb($this->output, [$moduleName]);
                $this->moduleRegistryUninstaller->removeModulesFromDeploymentConfig($this->output, [$moduleName]);
                break;
            case self::COMPONENT_THEME:
                $themePath = $this->themePackageInfo->getFullThemePath($componentName);
                $this->themeUninstaller->uninstallRegistry($this->output, [$themePath]);
                break;
        }
    }

    /**
     * Perform cleanup
     *
     * @return void
     */
    private function cleanUp()
    {
        $this->output->writeln('Cleaning cache');
        /** @var \Magento\Framework\App\Cache $cache */
        $cache = $this->objectManager->get('Magento\Framework\App\Cache');
        $cache->clean();
        /** @var \Magento\Framework\App\State\CleanupFiles $cleanupFiles */
        $cleanupFiles = $this->objectManager->get('Magento\Framework\App\State\CleanupFiles');
        $this->output->writeln('Cleaning generated files');
        $cleanupFiles->clearCodeGeneratedClasses();
        $this->output->writeln('Cleaning static view files');
        $cleanupFiles->clearMaterializedViewFiles();
    }
}
