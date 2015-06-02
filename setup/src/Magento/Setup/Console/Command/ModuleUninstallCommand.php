<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Composer\Console\Application;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\DependencyChecker;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\Resource;
use Magento\Setup\Model\ComposerInformation;
use Magento\Setup\Model\ModuleContext;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\UninstallCollector;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\BackupRollback;
use Magento\Setup\Model\ConsoleLogger;

/**
 * Command for uninstalling modules
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class ModuleUninstallCommand extends AbstractModuleCommand
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_REMOVE_DATA = 'remove-data';
    const INPUT_KEY_BACKUP_CODE = 'backup-code';

    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var FullModuleList
     */
    private $fullModuleList;

    /**
     * @var DeploymentConfig\Writer
     */
    private $writer;

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @var UninstallCollector
     */
    private $collector;

    /**
     * @var Resource
     */
    private $moduleResource;

    /**
     * @var DependencyChecker
     */
    private $dependencyChecker;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var ComposerInformation
     */
    private $composer;

    /**
     * @var File
     */
    private $file;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var Application
     */
    private $composerApp;

    /**
     * Constructor
     *
     * @param Application $composerApp
     * @param ComposerInformation $composer
     * @param DeploymentConfig $deploymentConfig
     * @param DeploymentConfig\Writer $writer
     * @param DirectoryList $directoryList
     * @param File $file
     * @param FullModuleList $fullModuleList
     * @param Loader $loader
     * @param MaintenanceMode $maintenanceMode
     * @param ObjectManagerProvider $objectManagerProvider
     * @param UninstallCollector $collector
     */
    public function __construct(
        Application $composerApp,
        ComposerInformation $composer,
        DeploymentConfig $deploymentConfig,
        DeploymentConfig\Writer $writer,
        DirectoryList $directoryList,
        File $file,
        FullModuleList $fullModuleList,
        Loader $loader,
        MaintenanceMode $maintenanceMode,
        ObjectManagerProvider $objectManagerProvider,
        UninstallCollector $collector
    ) {
        parent::__construct($objectManagerProvider);
        $this->composerApp = $composerApp;
        $this->composer = $composer;
        $this->deploymentConfig = $deploymentConfig;
        $this->directoryList = $directoryList;
        $this->writer = $writer;
        $this->maintenanceMode = $maintenanceMode;
        $this->fullModuleList = $fullModuleList;
        $this->packageInfo = $this->objectManager->get('Magento\Framework\Module\PackageInfoFactory')->create();
        $this->collector = $collector;
        $this->moduleResource = $this->objectManager->get('Magento\Framework\Module\Resource');
        $this->dependencyChecker = $this->objectManager->get('Magento\Framework\Module\DependencyChecker');
        $this->file = $file;
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Uninstalls modules installed by composer');
        $this->setName('module:uninstall');
        $this->addOption(
            self::INPUT_KEY_REMOVE_DATA,
            'r',
            InputOption::VALUE_NONE,
            'Remove data installed by module(s)'
        );
        $this->addOption(
            self::INPUT_KEY_BACKUP_CODE,
            null,
            InputOption::VALUE_NONE,
            'Take code backup (excluding temporary files)'
        );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function isModuleRequired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln(
                '<error>You cannot run this command because the Magento application is not installed.</error>'
            );
            return;
        }

        $modules = $input->getArgument(self::INPUT_KEY_MODULES);
        // validate modules input
        $messages = $this->validate($modules);
        if (!empty($messages)) {
            $output->writeln($messages);
            return;
        }

        // check dependencies
        $dependencyMessages = $this->checkDependencies($modules);
        if (!empty($dependencyMessages)) {
            $output->writeln($dependencyMessages);
            return;
        }

        // check if modules are already uninstalled
        $uninstallMessages = $this->checkUninstalled($modules);
        if (!empty($uninstallMessages)) {
            $output->writeln($uninstallMessages);
            return;
        }

        $output->writeln('<info>Enabling maintenance mode</info>');
        $this->maintenanceMode->set(true);

        try {
            if ($input->getOption(self::INPUT_KEY_BACKUP_CODE)) {
                $backupRollback = new BackupRollback(
                    $this->objectManager,
                    new ConsoleLogger($output),
                    $this->directoryList,
                    $this->file
                );
                $backupRollback->codeBackup();
            }
            if ($input->getOption(self::INPUT_KEY_REMOVE_DATA)) {
                $this->removeData($modules, $output);
            }
            $output->writeln('<info>Removing ' . implode(', ', $modules) . ' from module registry in database</info>');
            $this->removeModulesFromDb($modules);
            $output->writeln(
                '<info>Removing ' . implode(', ', $modules) .  ' from module list in deployment configuration</info>'
            );
            $this->removeModulesFromDeploymentConfig($modules);
            $output->writeln('<info>Removing code from Magento codebase:</info>');
            $this->removeCode($modules);
            $this->cleanup($input, $output);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } finally {
            $output->writeln('<info>Disabling maintenance mode</info>');
            $this->maintenanceMode->set(false);
        }
    }

    /**
     * Invoke remove data routine in each specified module
     *
     * @param string[] $modules
     * @param OutputInterface $output
     * @return void
     */
    private function removeData(array $modules, OutputInterface $output)
    {
        $output->writeln('<info>Removing data</info>');
        $uninstalls = $this->collector->collectUninstall();
        $setupModel = $this->objectManager->get('Magento\Setup\Module\Setup');
        foreach ($modules as $module) {
            if (isset($uninstalls[$module])) {
                $output->writeln("<info>Removing data of $module</info>");
                $uninstalls[$module]->uninstall(
                    $setupModel,
                    new ModuleContext($this->moduleResource->getDbVersion($module) ?: '')
                );
            } else {
                $output->writeln("<info>No data to clear in $module</info>");
            }
        }
    }

    /**
     * Run 'composer remove' to remove code
     *
     * @param array $modules
     */
    private function removeCode(array $modules)
    {
        $packages = [];
        foreach ($modules as $module) {
            $packages[] = $this->packageInfo->getPackageName($module);
        }
        $this->composerApp->setAutoExit(false);
        $this->composerApp->run(new ArrayInput(['command' => 'remove', 'packages' => $packages]));
    }

    /**
     * Validate list of modules against installed composer packages and return error messages
     *
     * @param string[] $modules
     * @return string[]
     */
    protected function validate(array $modules)
    {
        $messages = [];
        $unknownPackages = [];
        $unknownModules = [];
        $installedPackages = $this->composer->getRootRequiredPackages();
        foreach ($modules as $module) {
            if (array_search($this->packageInfo->getPackageName($module), $installedPackages) === false) {
                $unknownPackages[] = $module;
            }
            if (!$this->fullModuleList->has($module)) {
                $unknownModules[] = $module;
            }
        }
        $unknownPackages = array_diff($unknownPackages, $unknownModules);
        if (!empty($unknownPackages)) {
            $text = count($unknownPackages) > 1 ?
                ' are not installed composer packages' : ' is not an installed composer package';
            $messages[] = '<error>' . implode(', ', $unknownPackages) . $text . '</error>';
        }
        if (!empty($unknownModules)) {
            $messages[] = '<error>Unknown module(s): ' . implode(', ', $unknownModules) . '</error>';
        }
        return $messages;
    }

    /**
     * Check for dependencies to modules, return error messages
     *
     * @param string[] $modules
     * @return string[]
     */
    private function checkDependencies(array $modules)
    {
        $messages = [];
        $dependencies = $this->dependencyChecker->checkDependenciesWhenDisableModules(
            $modules,
            $this->fullModuleList->getNames()
        );
        foreach ($dependencies as $module => $dependingModules) {
            if (!empty($dependingModules)) {
                $messages[] =
                    "<error>Cannot uninstall module '$module' because the following module(s) depend on it:</error>" .
                    PHP_EOL . "\t<error>" . implode('</error>' . PHP_EOL . "\t<error>", array_keys($dependingModules)) .
                    "</error>";
            }
        }
        return $messages;
    }

    /**
     * Check for uninstalled modules, return error messages
     *
     * @param string[] $modules
     * @return string[]
     */
    private function checkUninstalled(array $modules)
    {
        $messages = [];
        foreach ($modules as $module) {
            if ($this->moduleResource->getDbVersion($module) === false) {
                $messages[] = "<error>$module is already uninstalled.</error>";
            }
        }
        return $messages;
    }

    /**
     * Removes module from setup_module table
     *
     * @param string[] $modules
     * @return void
     */
    private function removeModulesFromDb(array $modules)
    {
        /** @var \Magento\Setup\Module\DataSetup $setup */
        $setup = $this->objectManager->get('Magento\Setup\Module\DataSetup');
        foreach ($modules as $module) {
            $setup->deleteTableRow('setup_module', 'module', $module);
        }
    }

    /**
     * Removes module from deployment configuration
     *
     * @param string[] $modules
     * @return void
     */
    private function removeModulesFromDeploymentConfig(array $modules)
    {
        $existingModules = $this->deploymentConfig->getConfigData(ConfigOptionsListConstants::KEY_MODULES);
        $newSort = $this->loader->load($modules);
        $newModules = [];
        foreach (array_keys($newSort) as $module) {
            $newModules[$module] = $existingModules[$module];
        }
        $this->writer->saveConfig(
            [ConfigFilePool::APP_CONFIG => [ConfigOptionsListConstants::KEY_MODULES => $newModules]],
            true
        );
    }
}
