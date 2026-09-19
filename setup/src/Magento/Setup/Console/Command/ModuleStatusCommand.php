<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for displaying status of modules
 */
class ModuleStatusCommand extends AbstractSetupCommand
{
    public const NAME = 'module:status';
    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Displays status of modules')
            ->addArgument(
                'module-names',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Optional module name'
            )
            ->addOption('enabled', null, null, 'Print only enabled modules')
            ->addOption('disabled', null, null, 'Print only disabled modules');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $moduleNames = $input->getArgument('module-names');
        if (!empty($moduleNames)) {
            $overriddenModules = $this->getOverriddenModules();
            foreach ($moduleNames as $moduleName) {
                $this->showSpecificModule($moduleName, $overriddenModules, $output);
            }
            return Cli::RETURN_SUCCESS;
        }

        $onlyEnabled = $input->getOption('enabled');
        if ($onlyEnabled) {
            return $this->showEnabledModules($output);
        }

        $onlyDisabled = $input->getOption('disabled');
        if ($onlyDisabled) {
            return $this->showDisabledModules($output);
        }

        $output->writeln('<info>List of enabled modules:</info>');
        $this->showEnabledModules($output);
        $output->writeln('');

        $output->writeln("<info>List of disabled modules:</info>");
        $this->showDisabledModules($output);
        $output->writeln('');

        $this->showOverriddenModules($output);

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Specific module show
     *
     * @param string $moduleName
     * @param array $overriddenModules
     * @param OutputInterface $output
     * @return int
     */
    private function showSpecificModule(string $moduleName, array $overriddenModules, OutputInterface $output): int
    {
        $allModules = $this->getAllModules();
        if (!in_array($moduleName, $allModules->getNames(), true)) {
            $output->writeln($moduleName . ' : <error>Module does not exist</error>');
            return Cli::RETURN_FAILURE;
        }

        $note = isset($overriddenModules[$moduleName])
            ? sprintf(
                ' (overridden in app/etc/env.php; app/etc/config.php: %s)',
                $overriddenModules[$moduleName] ? 'disabled' : 'enabled'
            )
            : '';

        $enabledModules = $this->getEnabledModules();
        if (in_array($moduleName, $enabledModules->getNames(), true)) {
            $output->writeln($moduleName . ' : <info>Module is enabled</info>' . $note);
            return Cli::RETURN_FAILURE;
        }

        $output->writeln($moduleName . ' : <info> Module is disabled</info>' . $note);
        return Cli::RETURN_SUCCESS;
    }

    /**
     * Enable modules show
     *
     * @param OutputInterface $output
     * @return int
     */
    private function showEnabledModules(OutputInterface $output): int
    {
        $enabledModules = $this->getEnabledModules();
        $enabledModuleNames = $enabledModules->getNames();
        if (count($enabledModuleNames) === 0) {
            $output->writeln('None');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln(join("\n", $enabledModuleNames));

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Disabled modules show
     *
     * @param OutputInterface $output
     * @return int
     */
    private function showDisabledModules(OutputInterface $output): int
    {
        $disabledModuleNames = $this->getDisabledModuleNames();
        if (count($disabledModuleNames) === 0) {
            $output->writeln('None');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln(join("\n", $disabledModuleNames));

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Modules overridden in env.php show
     *
     * @param OutputInterface $output
     * @return void
     */
    private function showOverriddenModules(OutputInterface $output): void
    {
        $overriddenModules = $this->getOverriddenModules();
        if (count($overriddenModules) === 0) {
            return;
        }

        $output->writeln('<info>Modules overridden in app/etc/env.php:</info>');
        foreach ($overriddenModules as $moduleName => $isEnabled) {
            $output->writeln(sprintf(
                '%s : %s in env.php, %s in app/etc/config.php',
                $moduleName,
                $isEnabled ? 'enabled' : 'disabled',
                $isEnabled ? 'disabled' : 'enabled'
            ));
        }
        $output->writeln('');
    }

    /**
     * Returns modules whose env.php state differs from the state in config.php
     *
     * @return array
     */
    private function getOverriddenModules(): array
    {
        $reader = $this->objectManagerProvider->get()->get(Reader::class);
        $appConfig = $reader->load(ConfigFilePool::APP_CONFIG)[ConfigOptionsListConstants::KEY_MODULES] ?? [];
        $envConfig = $reader->load(ConfigFilePool::APP_ENV)[ConfigOptionsListConstants::KEY_MODULES] ?? [];

        $overriddenModules = [];
        foreach ($envConfig as $moduleName => $isEnabled) {
            if ((bool)$isEnabled !== (bool)($appConfig[$moduleName] ?? false)) {
                $overriddenModules[$moduleName] = (bool)$isEnabled;
            }
        }

        return array_intersect_key($overriddenModules, array_flip($this->getAllModules()->getNames()));
    }

    /**
     * Returns all modules
     *
     * @return FullModuleList
     */
    private function getAllModules(): FullModuleList
    {
        return $this->objectManagerProvider->get()
            ->create(FullModuleList::class);
    }

    /**
     * Returns enabled modules
     *
     * @return ModuleList
     */
    private function getEnabledModules(): ModuleList
    {
        return $this->objectManagerProvider->get()
            ->create(ModuleList::class);
    }

    /**
     * Returns disabled module names
     *
     * @return array
     */
    private function getDisabledModuleNames(): array
    {
        $fullModuleList = $this->getAllModules();
        $enabledModules = $this->getEnabledModules();

        return array_diff($fullModuleList->getNames(), $enabledModules->getNames());
    }
}
