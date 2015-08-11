<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Framework\Module\PackageInfo;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\ModuleUninstaller;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\Updater;
use Magento\Theme\Model\Theme\ThemeUninstaller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Job to remove a component. Run by Setup Cron Task
 */
class JobComponentUninstall extends AbstractJob
{
    /**
     * Component type
     */
    const COMPONENT_TYPE = 'type';

    /**
     * Component name
     */
    const COMPONENT_NAME = 'name';

    /**#@+
     * Component types
     */
    const COMPONENT_MODULE = 'module';
    const COMPONENT_THEME = 'theme';
    const COMPONENT_LANGUAGE = 'language';
    /**#@-*/

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ComponentUninstallerFactory
     */
    private $componentUninstallerFactory;

    /**
     * @var Updater
     */
    private $updater;

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * Constructor
     *
     * @param ComponentUninstallerFactory $componentUninstallerFactory
     * @param ObjectManagerProvider $objectManagerProvider
     * @param OutputInterface $output
     * @param Status $status
     * @param Updater $updater
     * @param array $name
     * @param array $params
     */
    public function __construct(
        ComponentUninstallerFactory $componentUninstallerFactory,
        ObjectManagerProvider $objectManagerProvider,
        OutputInterface $output,
        Status $status,
        Updater $updater,
        $name,
        $params = []
    ) {
        $this->objectManager = $objectManagerProvider->get();;
        $this->componentUninstallerFactory = $componentUninstallerFactory;
        $this->updater = $updater;
        $this->packageInfo = $this->objectManager->get('Magento\Framework\Module\PackageInfoFactory')->create();
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
        if (!isset($component[self::COMPONENT_TYPE]) || !isset($component[self::COMPONENT_NAME])) {
            throw new \RuntimeException('Job parameter format is incorrect');
        }
        $type = $component[self::COMPONENT_TYPE];
        $componentName = $component[self::COMPONENT_NAME];

        if (!in_array($type, [self::COMPONENT_MODULE, self::COMPONENT_THEME, self::COMPONENT_LANGUAGE])) {
            throw new \RuntimeException('Unknown component type');
        }

        $options = [];
        $skip = false;
        switch ($type) {
            case self::COMPONENT_MODULE:
                // convert to module name
                $componentName = $this->packageInfo->getModuleName($componentName);
                $options[ModuleUninstaller::OPTION_UNINSTALL_DATA] = isset($this->params['dataOption']) ?
                    $this->params['dataOption'] : false;
                $options[ModuleUninstaller::OPTION_UNINSTALL_REGISTRY] = true;
                break;
            case self::COMPONENT_THEME:
                $options[ThemeUninstaller::OPTION_UNINSTALL_REGISTRY] = true;
                break;
            case self::COMPONENT_LANGUAGE:
                $skip = true;
                break;
        }
        if (!$skip) {
            $this->createAndRunUninstaller($type, $componentName, $options);
        }
    }

    /**
     * Create the command and run it
     *
     * @param string $type
     * @param string $componentName
     * @param array $options
     * @return void
     */
    private function createAndRunUninstaller($type, $componentName, array $options)
    {
        $uninstaller = $this->componentUninstallerFactory->create($type);
        $uninstaller->uninstall($this->output, [$componentName], $options);
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
