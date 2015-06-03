<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Composer\Remove;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\DependencyChecker;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\Resource;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\ModuleContext;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\UninstallCollector;
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
    const INPUT_KEY_BACKUP_DATA = 'backup-data';

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
     * @var Remove
     */
    private $remove;

    /**
     * Constructor
     *
     * @param ComposerInformation $composer
     * @param DeploymentConfig $deploymentConfig
     * @param DeploymentConfig\Writer $writer
     * @param DirectoryList $directoryList
     * @param File $file
     * @param FullModuleList $fullModuleList
     * @param Loader $loader
     * @param MaintenanceMode $maintenanceMode
     * @param ObjectManagerProvider $objectManagerProvider
     * @param Remove $remove
     * @param UninstallCollector $collector
     */
    public function __construct(
        ComposerInformation $composer,
        DeploymentConfig $deploymentConfig,
        DeploymentConfig\Writer $writer,
        DirectoryList $directoryList,
        File $file,
        FullModuleList $fullModuleList,
        Loader $loader,
        MaintenanceMode $maintenanceMode,
        ObjectManagerProvider $objectManagerProvider,
        Remove $remove,
        UninstallCollector $collector
    ) {
        parent::__construct($objectManagerProvider);
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
        $this->remove = $remove;
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
        $this->addOption(
            self::INPUT_KEY_BACKUP_DATA,
            null,
            InputOption::VALUE_NONE,
            'Take complete database and media backup'
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

        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog->askConfirmation(
            $output,
            '<question>You are about to remove code and database tables. Are you sure?[y/N]</question>'
        ) && $input->isInteractive()) {
            return;
        }

        $output->writeln('<info>Enabling maintenance mode</info>');
        $this->maintenanceMode->set(true);

        try {
            if ($input->getOption(self::INPUT_KEY_BACKUP_CODE)) {
                $codeBackup = new BackupRollback(
                    $this->objectManager,
                    new ConsoleLogger($output),
                    $this->directoryList,
                    $this->file
                );
                $codeBackup->codeBackup();
            }
            $dataBackupOption = $input->getOption(self::INPUT_KEY_BACKUP_DATA);
            if ($dataBackupOption) {
                $dataBackup = new BackupRollback(
                    $this->objectManager,
                    new ConsoleLogger($output),
                    $this->directoryList,
                    $this->file
                );
                $dataBackup->dataBackup();
            }

            if ($input->getOption(self::INPUT_KEY_REMOVE_DATA)) {
                $this->removeData($modules, $output, $dataBackupOption);
            } else {
                if (!empty($this->collector->collectUninstall())) {
                    if ($dialog->askConfirmation(
                        $output,
                        '<question>You are about to remove a module(s) that might have database data. '
                        . 'Do you want to remove the data from database?[y/N]</question>'
                    ) || !$input->isInteractive()) {
                        $this->removeData($modules, $output, $dataBackupOption);
                    }
                } else {
                    $output->writeln(
                        '<info>You are about to remove a module(s) that might have database data. '
                        . 'Remove the database data manually after uninstalling, if desired.</info>'
                    );
                }
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
     * @param bool $dataBackupOption
     * @return void
     */
    private function removeData(array $modules, OutputInterface $output, $dataBackupOption)
    {
        if (!$dataBackupOption) {
            $output->writeln('<error>You are removing data without a backup.</error>');
        } else {
            $output->writeln('<info>Removing data</info>');
        }
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
     * @return void
     */
    private function removeCode(array $modules)
    {
        $packages = [];
        foreach ($modules as $module) {
            $packages[] = $this->packageInfo->getPackageName($module);
        }
        $this->remove->remove($packages);

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
