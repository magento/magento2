<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Setup\Model\Updater;
use Magento\Setup\Model\Cron\Queue;
use Magento\Framework\Composer\ComposerInformation;

/**
 * Job to remove a component. Run by Setup Cron Task
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @since 2.0.0
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

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var \Magento\Setup\Model\Updater
     * @since 2.0.0
     */
    private $updater;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     * @since 2.0.0
     */
    private $composerInformation;

    /**
     * @var Helper\ModuleUninstall
     * @since 2.0.0
     */
    private $moduleUninstall;

    /**
     * @var Helper\ThemeUninstall
     * @since 2.0.0
     */
    private $themeUninstall;

    /**
     * @var \Magento\Setup\Model\Cron\Queue
     * @since 2.0.0
     */
    private $queue;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Composer\ComposerInformation $composerInformation
     * @param Helper\ModuleUninstall $moduleUninstall
     * @param Helper\ThemeUninstall $themeUninstall
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Magento\Setup\Model\Cron\Queue $queue
     * @param \Magento\Setup\Model\Cron\Status $status
     * @param \Magento\Setup\Model\Updater $updater
     * @param string $name
     * @param array $params
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Composer\ComposerInformation $composerInformation,
        Helper\ModuleUninstall $moduleUninstall,
        Helper\ThemeUninstall $themeUninstall,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider,
        \Symfony\Component\Console\Output\OutputInterface $output,
        \Magento\Setup\Model\Cron\Queue $queue,
        \Magento\Setup\Model\Cron\Status $status,
        \Magento\Setup\Model\Updater $updater,
        $name,
        $params = []
    ) {
        $this->composerInformation = $composerInformation;
        $this->moduleUninstall = $moduleUninstall;
        $this->themeUninstall = $themeUninstall;
        $this->objectManager = $objectManagerProvider->get();
        $this->updater = $updater;
        $this->queue = $queue;
        parent::__construct($output, $status, $objectManagerProvider, $name, $params);
    }

    /**
     * Run remove component job
     *
     * @return void
     * @throw \RuntimeException
     * @since 2.0.0
     */
    public function execute()
    {
        if (!isset($this->params['components']) || !is_array($this->params['components'])) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException('Job parameter format is incorrect');
        }
        $components = $this->params['components'];
        foreach ($components as $component) {
            $this->executeComponent($component);
        }
        $this->queue->addJobs(
            [['name' => JobFactory::JOB_STATIC_REGENERATE, 'params' => []]]
        );
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
     * @since 2.0.0
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
            ComposerInformation::MODULE_PACKAGE_TYPE,
            ComposerInformation::THEME_PACKAGE_TYPE,
            ComposerInformation::LANGUAGE_PACKAGE_TYPE,
            ComposerInformation::COMPONENT_PACKAGE_TYPE
        ])) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException('Unknown component type');
        }

        switch ($type) {
            case ComposerInformation::MODULE_PACKAGE_TYPE:
                $dataOption = isset($this->params[self::DATA_OPTION]) && $this->params[self::DATA_OPTION] === 'true';
                $this->moduleUninstall->uninstall(
                    $this->output,
                    $componentName,
                    $dataOption
                );
                break;
            case ComposerInformation::THEME_PACKAGE_TYPE:
                $this->themeUninstall->uninstall($this->output, $componentName);
                break;
        }
    }
}
