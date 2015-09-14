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
    const COMPONENT = 'magento2-component';
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
     * @var \Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    /**
     * @var Helper\ModuleUninstall
     */
    private $moduleUninstall;

    /**
     * @var Helper\ThemeUninstall
     */
    private $themeUninstall;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Composer\ComposerInformation $composerInformation
     * @param Helper\ModuleUninstall $moduleUninstall
     * @param Helper\ThemeUninstall $themeUninstall
     * @param ObjectManagerProvider $objectManagerProvider
     * @param OutputInterface $output
     * @param Status $status
     * @param Updater $updater
     * @param string $name
     * @param array $params
     */
    public function __construct(
        \Magento\Framework\Composer\ComposerInformation $composerInformation,
        Helper\ModuleUninstall $moduleUninstall,
        Helper\ThemeUninstall $themeUninstall,
        ObjectManagerProvider $objectManagerProvider,
        OutputInterface $output,
        Status $status,
        Updater $updater,
        $name,
        $params = []
    ) {
        $this->composerInformation = $composerInformation;
        $this->moduleUninstall = $moduleUninstall;
        $this->themeUninstall = $themeUninstall;
        $this->objectManager = $objectManagerProvider->get();
        $this->updater = $updater;
        parent::__construct($output, $status, $objectManagerProvider, $name, $params);
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
            $this->status->toggleUpdateError(true);
            throw new \RunTimeException('Job parameter format is incorrect');
        }
        $components = $this->params['components'];
        foreach ($components as $component) {
            $this->executeComponent($component);
        }
        $this->cleanUp();
        $errorMessage = $this->updater->createUpdaterTask($components, Updater::TASK_TYPE_UNINSTALL);
        if ($errorMessage) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException($errorMessage);
        }
    }

    /**
     * Execute uninstall on a component
     *
     * @param array $component
     * @return void
     * @throw \RuntimeException
     */
    private function executeComponent(array $component)
    {
        if (!isset($component[self::COMPONENT_NAME])) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException('Job parameter format is incorrect');
        }

        $componentName = $component[self::COMPONENT_NAME];
        $installedPackages = $this->composerInformation->getInstalledMagentoPackages();
        if (isset($installedPackages[$componentName]['type'])) {
            $type = $installedPackages[$componentName]['type'];
        } else {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException('Component type not set');
        }

        if (!in_array($type, [
            self::COMPONENT_MODULE,
            self::COMPONENT_THEME,
            self::COMPONENT_LANGUAGE,
            self::COMPONENT
        ])) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException('Unknown component type');
        }

        switch ($type) {
            case self::COMPONENT_MODULE:
                $dataOption = isset($this->params[self::DATA_OPTION]) && $this->params[self::DATA_OPTION] === 'true';
                $this->moduleUninstall->uninstall(
                    $this->output,
                    $componentName,
                    $dataOption
                );
                break;
            case self::COMPONENT_THEME:
                $this->themeUninstall->uninstall($this->output, $componentName);
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
