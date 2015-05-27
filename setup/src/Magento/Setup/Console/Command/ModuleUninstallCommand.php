<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Module\DependencyChecker;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\Resource;
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
 */
class ModuleUninstallCommand extends AbstractModuleCommand
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_REMOVE_DATA = 'remove-data';
    const INPUT_KEY_CODE_BACKUP = 'code-backup';

    /**
     * @var Decoder
     */
    private $decoder;

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
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var BackupRollback
     */
    private $backupRollback;

    /**
     * Constructor
     *
     * @param DeploymentConfig $deploymentConfig
     * @param DeploymentConfig\Writer $writer
     * @param FullModuleList $fullModuleList
     * @param MaintenanceMode $maintenanceMode
     * @param ObjectManagerProvider $objectManagerProvider
     * @param UninstallCollector $collector
     * @param BackupRollback $backupRollback
     */
    public function __construct(
        Decoder $decoder,
        DeploymentConfig $deploymentConfig,
        DeploymentConfig\Writer $writer,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        FullModuleList $fullModuleList,
        MaintenanceMode $maintenanceMode,
        ObjectManagerProvider $objectManagerProvider,
        UninstallCollector $collector,
        BackupRollback $backupRollback
    ) {
        parent::__construct($objectManagerProvider);
        $this->decoder = $decoder;
        $this->deploymentConfig = $deploymentConfig;
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->writer = $writer;
        $this->maintenanceMode = $maintenanceMode;
        $this->fullModuleList = $fullModuleList;
        $this->packageInfo = $this->objectManagerProvider
            ->get()
            ->get('Magento\Framework\Module\PackageInfoFactory')
            ->create();
        $this->collector = $collector;
        $this->moduleResource = $this->objectManagerProvider->get()->get('Magento\Framework\Module\Resource');
        $this->dependencyChecker = $this->objectManagerProvider->get()
            ->get('Magento\Framework\Module\DependencyChecker');
        $this->backupRollback = $backupRollback;
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
            'Removes data installed by module(s)'
        );
        $this->addOption(
            self::INPUT_KEY_CODE_BACKUP,
            null,
            InputOption::VALUE_NONE,
            'Take code backup (excludes \'' . DirectoryList::STATIC_VIEW . '\' and  \'' . DirectoryList::VAR_DIR . '\')'
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
        if ($input->getOption(self::INPUT_KEY_CODE_BACKUP)) {
            $this->backupRollback->codeBackup($this->objectManagerProvider->get(), new ConsoleLogger($output));
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
            $output->writeln('<error>Cannot uninstall because of depending module(s):</error>');
            $output->writeln($dependencyMessages);
            return;
        }

        $output->writeln('<info>Enabling maintenance mode</info>');
        $this->maintenanceMode->set(true);

        try {
            if ($input->getOption(self::INPUT_KEY_CODE_BACKUP)) {
                $this->backupRollback->codeBackup($this->objectManagerProvider->get(), new ConsoleLogger($output));
            }
            if ($input->getOption(self::INPUT_KEY_REMOVE_DATA)) {
                $uninstalls = $this->collector->collectUninstall();
                foreach ($modules as $module) {
                    if (isset($uninstalls[$module])) {
                        $output->writeln("<info>Removing data of $module</info>");
                        $uninstalls[$module]->uninstall(
                            $this->objectManagerProvider->get()->create('Magento\Setup\Module\Setup'),
                            new ModuleContext($this->moduleResource->getDbVersion($module) ?: '')
                        );
                    }
                }
            }
            $this->removeModulesFromDb($modules);
            $output->writeln('<info>Removing ' . implode(', ', $modules) . ' from module registry in database</info>');
            $this->removeModulesFromDeploymentConfig($modules);
            $output->writeln(
                '<info>Removing ' . implode(', ', $modules) .  ' from module list in deployment configuration</info>'
            );
            $this->cleanup($input, $output);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        } finally {
            $output->writeln('<info>Disabling maintenance mode</info>');
            $this->maintenanceMode->set(false);
        }
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
        $installedPackages = $this->parsePackages();
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
     * Parse output from root composer.json into list of package names
     *
     * @return array
     */
    private function parsePackages()
    {
        $packages = [];
        $directoryRead = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $rawJson = $directoryRead->readFile('composer.json');
        $data = $this->decoder->decode($rawJson);
        foreach (array_keys($data['require']) as $package) {
            if (count(explode('/', $package)) == 2) {
                $packages[] = $package;
            }
        }
        return $packages;
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
                $messages[] = "<error>Module(s) depending on $module: " .
                    implode(', ', array_keys($dependingModules)) . "</error>";
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
        $setup = $this->objectManagerProvider->get()->get('Magento\Setup\Module\DataSetup');
        $setup->startSetup();
        foreach ($modules as $module) {
            $setup->deleteTableRow('setup_module', 'module', $module);
        }
        $setup->endSetup();
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
        foreach ($modules as $module) {
            unset($existingModules[$module]);
        }
        $this->writer->saveConfig(
            [ConfigFilePool::APP_CONFIG => [ConfigOptionsListConstants::KEY_MODULES => $existingModules]],
            true
        );
    }
}
