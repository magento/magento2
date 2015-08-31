<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\Remove;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Module\DependencyChecker;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\Resource;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Model\ModuleContext;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\UninstallCollector;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command for uninstalling modules
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class ModuleUninstallCommand extends AbstractModuleCommand
{
    /**
     * Names of input options
     */
    const INPUT_KEY_REMOVE_DATA = 'remove-data';
    const INPUT_KEY_BACKUP_CODE = 'backup-code';
    const INPUT_KEY_BACKUP_MEDIA = 'backup-media';
    const INPUT_KEY_BACKUP_DB = 'backup-db';

    /**
     * Maintenance mode
     *
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * Deployment Configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Full module list
     *
     * @var FullModuleList
     */
    private $fullModuleList;

    /**
     * Deployment Configuration writer
     *
     * @var DeploymentConfig\Writer
     */
    private $writer;

    /**
     * Module package info
     *
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * Uninstall classes collector
     *
     * @var UninstallCollector
     */
    private $collector;

    /**
     * Module Resource
     *
     * @var Resource
     */
    private $moduleResource;

    /**
     * Composer general dependency checker
     *
     * @var DependencyChecker
     */
    private $dependencyChecker;

    /**
     * Root composer.json information
     *
     * @var ComposerInformation
     */
    private $composer;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * Code remover
     *
     * @var Remove
     */
    private $remove;

    /**
     * BackupRollback factory
     *
     * @var BackupRollbackFactory
     */
    private $backupRollbackFactory;

    /**
     * Constructor
     *
     * @param ComposerInformation $composer
     * @param DeploymentConfig $deploymentConfig
     * @param DeploymentConfig\Writer $writer
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
        $this->writer = $writer;
        $this->maintenanceMode = $maintenanceMode;
        $this->fullModuleList = $fullModuleList;
        $this->packageInfo = $this->objectManager->get('Magento\Framework\Module\PackageInfoFactory')->create();
        $this->collector = $collector;
        $this->moduleResource = $this->objectManager->get('Magento\Framework\Module\Resource');
        $this->dependencyChecker = $this->objectManager->get('Magento\Framework\Module\DependencyChecker');
        $this->loader = $loader;
        $this->remove = $remove;
        $this->backupRollbackFactory = $this->objectManager->get('Magento\Framework\Setup\BackupRollbackFactory');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_REMOVE_DATA,
                'r',
                InputOption::VALUE_NONE,
                'Remove data installed by module(s)'
            ),
            new InputOption(
                self::INPUT_KEY_BACKUP_CODE,
                null,
                InputOption::VALUE_NONE,
                'Take code and configuration files backup (excluding temporary files)'
            ),
            new InputOption(
                self::INPUT_KEY_BACKUP_MEDIA,
                null,
                InputOption::VALUE_NONE,
                'Take media backup'
            ),
            new InputOption(
                self::INPUT_KEY_BACKUP_DB,
                null,
                InputOption::VALUE_NONE,
                'Take complete database backup'
            ),
        ];
        $this->setName('module:uninstall')
            ->setDescription('Uninstalls modules installed by composer')
            ->setDefinition($options);
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'You are about to remove code and/or database tables. Are you sure?[y/N]',
            false
        );
        if (!$helper->ask($input, $output, $question) && $input->isInteractive()) {
            return;
        }
        try {
            $output->writeln('<info>Enabling maintenance mode</info>');
            $this->maintenanceMode->set(true);
            $this->takeBackup($input, $output);
            $dbBackupOption = $input->getOption(self::INPUT_KEY_BACKUP_DB);
            if ($input->getOption(self::INPUT_KEY_REMOVE_DATA)) {
                $this->removeData($modules, $output, $dbBackupOption);
            } else {
                if (!empty($this->collector->collectUninstall())) {
                    $question = new ConfirmationQuestion(
                        'You are about to remove a module(s) that might have database data. '
                        . 'Do you want to remove the data from database?[y/N]',
                        false
                    );
                    if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
                        $this->removeData($modules, $output, $dbBackupOption);
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
            $output->writeln('<info>Disabling maintenance mode</info>');
            $this->maintenanceMode->set(false);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->writeln('<error>Please disable maintenance mode after you resolved above issues</error>');
        }
    }

    /**
     * Check backup options and take backup appropriately
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    private function takeBackup(InputInterface $input, OutputInterface $output)
    {
        $time = time();
        if ($input->getOption(self::INPUT_KEY_BACKUP_CODE)) {
            $codeBackup = $this->backupRollbackFactory->create($output);
            $codeBackup->codeBackup($time);
        }
        if ($input->getOption(self::INPUT_KEY_BACKUP_MEDIA)) {
            $mediaBackup = $this->backupRollbackFactory->create($output);
            $mediaBackup->codeBackup($time, Factory::TYPE_MEDIA);
        }
        if ($input->getOption(self::INPUT_KEY_BACKUP_DB)) {
            $dbBackup = $this->backupRollbackFactory->create($output);
            $dbBackup->dbBackup($time);
        }
    }

    /**
     * Invoke remove data routine in each specified module
     *
     * @param string[] $modules
     * @param OutputInterface $output
     * @param bool $dbBackupOption
     * @return void
     */
    private function removeData(array $modules, OutputInterface $output, $dbBackupOption)
    {
        if (!$dbBackupOption) {
            $output->writeln('<error>You are removing data without a database backup.</error>');
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
