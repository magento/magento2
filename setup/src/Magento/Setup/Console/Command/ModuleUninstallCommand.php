<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Composer\Console\Application;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleUninstallCommand extends AbstractModuleCommand
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_REMOVE_DATA = 'remove-data';

    /**
     * @var Application
     */
    private $composerApp;

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
     * Constructor
     *
     * @param DeploymentConfig $deploymentConfig
     * @param DeploymentConfig\Writer $writer
     * @param FullModuleList $fullModuleList
     * @param MaintenanceMode $maintenanceMode
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        Application $composerApp,
        DeploymentConfig $deploymentConfig,
        DeploymentConfig\Writer $writer,
        FullModuleList $fullModuleList,
        MaintenanceMode $maintenanceMode,
        ObjectManagerProvider $objectManagerProvider
    ) {
        parent::__construct($objectManagerProvider);
        $this->composerApp = $composerApp;
        $this->composerApp->setAutoExit(false);
        $this->deploymentConfig = $deploymentConfig;
        $this->writer = $writer;
        $this->maintenanceMode = $maintenanceMode;
        $this->fullModuleList = $fullModuleList;
        $this->packageInfo = $this->objectManagerProvider
            ->get()
            ->get('Magento\Framework\Module\PackageInfoFactory')
            ->create();
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

        parent::configure();
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
        $messages = $this->validate($modules);
        if (!empty($messages)) {
            $output->writeln(implode(PHP_EOL, $messages));
            return;
        }

        // check dependencies
        $dependencies = $this->checkDependencies($modules);
        if (!empty($dependencies)) {
            $output->writeln('Cannot uninstall because of dependencies: ' . implode(',', $dependencies));
            return;
        }

        $this->maintenanceMode->set(true);

        try {
            if ($input->getOption(self::INPUT_KEY_REMOVE_DATA)) {
                // TODO: collection interface and remove data
            }
            $this->removeModulesFromDb($modules);
            $output->writeln('<info>Updated module registry in database</info>');
            $this->removeModulesFromDeploymentConfig($modules);
            $output->writeln('<info>Updated module list in deployment configuration</info>');
            $this->cleanup($input, $output);
            $this->maintenanceMode->set(false);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $this->maintenanceMode->set(false);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validate(array $modules)
    {
        $messages = [];
        $unknownPackages = [];
        $unknownModules = [];
        $buffer = new BufferedOutput();
        $this->composerApp->run(new ArrayInput(['command' => 'show', '-i' => true]), $buffer);
        $installedPackages = $this->parsePackages($buffer->fetch());
        foreach ($modules as $module) {
            if (!array_search($this->packageInfo->getPackageName($module), $installedPackages)) {
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
        $messages = array_merge($messages, parent::validate($modules));
        return $messages;
    }

    /**
     * Parse output from composer commands into list of package names
     *
     * @param string $output
     * @return array
     */
    private function parsePackages($output)
    {
        $parsed = [];
        foreach (explode(PHP_EOL, $output) as $package) {
            $package = explode(' ', $package)[0];
            if (count(explode('/', $package)) == 2) {
                $parsed[] = $package;
            }
        }
        return $parsed;
    }

    /**
     * Check for dependencies to modules
     *
     * @param string[] $modules
     * @return string[]
     */
    private function checkDependencies(array $modules)
    {
        $dependencies = [];
        $packagesToUninstall = [];
        foreach ($modules as $module) {
            $buffer = new BufferedOutput();
            $packageName = $this->packageInfo->getPackageName($module);
            $packagesToUninstall[] = $packageName;
            if ($packageName !== '') {
                $this->composerApp->run(new ArrayInput(['command' => 'depends', 'package' => $packageName]), $buffer);
                $dependencies = array_unique(array_merge($dependencies, $this->parsePackages($buffer->fetch())));
            }
        }
        $dependencies = array_diff($dependencies, $packagesToUninstall);
        return $dependencies;
    }

    /**
     * @param string[] $modules
     * @void
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
