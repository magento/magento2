<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Setup\Model\Updater;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\Cron\Queue;

/**
 * Job to remove a component. Run by Setup Cron Task
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Setup\Model\Updater
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
     * @var \Magento\Setup\Model\Cron\Queue
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
}
