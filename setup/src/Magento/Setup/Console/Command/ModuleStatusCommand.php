<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Setup\Console\Command;

use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Command for displaying status of modules
 */
class ModuleStatusCommand extends AbstractSetupCommand
{
    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Inject dependencies
     *
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('module:status')
            ->setDescription('Displays status of modules')
            ->addArgument('module', InputArgument::OPTIONAL, 'Optional module name')
            ->addOption('enabled', null, null, 'Print only enabled modules')
            ->addOption('disabled', null, null, 'Print only disabled modules');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleName = (string)$input->getArgument('module');
        if ($moduleName) {
            return $this->showSpecificModule($moduleName, $output);
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
    }

    /**
     * @param string $moduleName
     * @param OutputInterface $output
     */
    private function showSpecificModule(string $moduleName, OutputInterface $output)
    {
        $allModules = $this->getAllModules();
        if (!in_array($moduleName, $allModules->getNames())) {
            $output->writeln('<error>Module does not exist</error>');
            return Cli::RETURN_FAILURE;
        }

        $enabledModules = $this->getEnabledModules();
        if (in_array($moduleName, $enabledModules->getNames())) {
            $output->writeln('<info>Module is enabled</info>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Module is disabled</info>');
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * @param OutputInterface $output
     */
    private function showEnabledModules(OutputInterface $output)
    {
        $enabledModules = $this->getEnabledModules();
        $enabledModuleNames = $enabledModules->getNames();
        if (count($enabledModuleNames) === 0) {
            $output->writeln('None');
            return Cli::RETURN_FAILURE;
        }
        
        $output->writeln(join("\n", $enabledModuleNames));
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * @param OutputInterface $output
     */
    private function showDisabledModules(OutputInterface $output)
    {
        $disabledModuleNames = $this->getDisabledModuleNames();
        if (count($disabledModuleNames) === 0) {
            $output->writeln('None');
            return Cli::RETURN_FAILURE;
        }
      
        $output->writeln(join("\n", $disabledModuleNames));
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * @return FullModuleList
     */
    private function getAllModules(): FullModuleList
    {
        return $this->objectManagerProvider->get()->create(FullModuleList::class);
    }

    /**
     * @return ModuleList
     */
    private function getEnabledModules(): ModuleList
    {
        return $this->objectManagerProvider->get()->create(ModuleList::class);
    }

    /**
     * @return array
     */
    private function getDisabledModuleNames(): array
    {
        $fullModuleList = $this->getAllModules();
        $enabledModules = $this->getEnabledModules();
        return array_diff($fullModuleList->getNames(), $enabledModules->getNames());
    }
}
