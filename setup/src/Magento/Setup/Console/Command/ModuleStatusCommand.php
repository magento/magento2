<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Console\Command;

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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleNames = $input->getArgument('module-names');
        if (!empty($moduleNames)) {
            foreach ($moduleNames as $moduleName) {
                $this->showSpecificModule($moduleName, $output);
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

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Specific module show
     *
     * @param string $moduleName
     * @param OutputInterface $output
     * @return int
     */
    private function showSpecificModule(string $moduleName, OutputInterface $output): int
    {
        $allModules = $this->getAllModules();
        if (!in_array($moduleName, $allModules->getNames(), true)) {
            $output->writeln($moduleName . ' : <error>Module does not exist</error>');
            return Cli::RETURN_FAILURE;
        }

        $enabledModules = $this->getEnabledModules();
        if (in_array($moduleName, $enabledModules->getNames(), true)) {
            $output->writeln($moduleName . ' : <info>Module is enabled</info>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln($moduleName . ' : <info> Module is disabled</info>');
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
