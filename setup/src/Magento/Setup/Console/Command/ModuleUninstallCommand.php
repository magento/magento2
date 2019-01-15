<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\Console\MaintenanceModeEnabler;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Console\Cli;
use Magento\Framework\Module\DependencyChecker;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Model\ModuleRegistryUninstaller;
use Magento\Setup\Model\ModuleUninstaller;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\Setup\Patch\PatchApplier;
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
    const INPUT_KEY_NON_COMPOSER_MODULE = 'non-composer';

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
     * BackupRollback factory
     *
     * @var BackupRollbackFactory
     */
    private $backupRollbackFactory;

    /**
     * Module Uninstaller
     *
     * @var ModuleUninstaller
     */
    private $moduleUninstaller;

    /**
     * Module Registry uninstaller
     *
     * @var ModuleRegistryUninstaller
     */
    private $moduleRegistryUninstaller;

    /**
     * @var MaintenanceModeEnabler
     */
    private $maintenanceModeEnabler;

    /**
     * @var PatchApplier
     */
    private $patchApplier;

    /**
     * Constructor
     *
     * @param ComposerInformation $composer
     * @param DeploymentConfig $deploymentConfig
     * @param FullModuleList $fullModuleList
     * @param MaintenanceMode $maintenanceMode deprecated, use $maintenanceModeEnabler instead
     * @param ObjectManagerProvider $objectManagerProvider
     * @param UninstallCollector $collector
     * @param ModuleUninstaller $moduleUninstaller
     * @param ModuleRegistryUninstaller $moduleRegistryUninstaller
     * @param MaintenanceModeEnabler $maintenanceModeEnabler
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ComposerInformation $composer,
        DeploymentConfig $deploymentConfig,
        FullModuleList $fullModuleList,
        MaintenanceMode $maintenanceMode,
        ObjectManagerProvider $objectManagerProvider,
        UninstallCollector $collector,
        ModuleUninstaller $moduleUninstaller,
        ModuleRegistryUninstaller $moduleRegistryUninstaller,
        MaintenanceModeEnabler $maintenanceModeEnabler = null
    ) {
        parent::__construct($objectManagerProvider);
        $this->composer = $composer;
        $this->deploymentConfig = $deploymentConfig;
        $this->fullModuleList = $fullModuleList;
        $this->packageInfo = $this->objectManager->get(\Magento\Framework\Module\PackageInfoFactory::class)->create();
        $this->collector = $collector;
        $this->dependencyChecker = $this->objectManager->get(\Magento\Framework\Module\DependencyChecker::class);
        $this->backupRollbackFactory = $this->objectManager->get(\Magento\Framework\Setup\BackupRollbackFactory::class);
        $this->moduleUninstaller = $moduleUninstaller;
        $this->moduleRegistryUninstaller = $moduleRegistryUninstaller;
        $this->maintenanceModeEnabler =
            $maintenanceModeEnabler ?: $this->objectManager->get(MaintenanceModeEnabler::class);
    }

    /**
     * @return PatchApplier
     */
    private function getPatchApplier()
    {
        if (!$this->patchApplier) {
            $this->patchApplier = $this
                ->objectManager->get(PatchApplier::class);
        }

        return $this->patchApplier;
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
            new InputOption(
                self::INPUT_KEY_NON_COMPOSER_MODULE,
                null,
                InputOption::VALUE_NONE,
                'All modules, that will be past here will be non composer based'
            )
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln(
                '<error>You cannot run this command because the Magento application is not installed.</error>'
            );
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }

        $modules = $input->getArgument(self::INPUT_KEY_MODULES);

        if ($input->getOption(self::INPUT_KEY_NON_COMPOSER_MODULE)) {
            foreach ($modules as $moduleName) {
                $this->getPatchApplier()->revertDataPatches($moduleName);
            }

            return Cli::RETURN_SUCCESS;
        }

        // validate modules input
        $messages = $this->validate($modules);
        if (!empty($messages)) {
            $output->writeln($messages);
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }

        // check dependencies
        $dependencyMessages = $this->checkDependencies($modules);
        if (!empty($dependencyMessages)) {
            $output->writeln($dependencyMessages);
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'You are about to remove code and/or database tables. Are you sure?[y/N]',
            false
        );
        if (!$helper->ask($input, $output, $question) && $input->isInteractive()) {
            return Cli::RETURN_FAILURE;
        }

        $result = $this->maintenanceModeEnabler->executeInMaintenanceMode(
            function () use ($input, $output, $modules, $helper) {
                try {
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
                    $this->moduleRegistryUninstaller->removeModulesFromDb($output, $modules);
                    $this->moduleRegistryUninstaller->removeModulesFromDeploymentConfig($output, $modules);
                    $this->moduleUninstaller->uninstallCode($output, $modules);
                    $this->cleanup($input, $output);

                    return Cli::RETURN_SUCCESS;
                } catch (\Exception $e) {
                    $output->writeln('<error>' . $e->getMessage() . '</error>');
                    $output->writeln('<error>Please disable maintenance mode after you resolved above issues</error>');
                    return Cli::RETURN_FAILURE;
                }
            },
            $output,
            true
        );

        return $result;
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
            $this->setAreaCode();
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
        $this->moduleUninstaller->uninstallData($output, $modules);
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
     * Sets area code to start a session for database backup and rollback
     *
     * @return void
     */
    private function setAreaCode()
    {
        $areaCode = 'adminhtml';
        /** @var \Magento\Framework\App\State $appState */
        $appState = $this->objectManager->get(\Magento\Framework\App\State::class);
        $appState->setAreaCode($areaCode);
        /** @var \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader */
        $configLoader = $this->objectManager->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class);
        $this->objectManager->configure($configLoader->load($areaCode));
    }
}
