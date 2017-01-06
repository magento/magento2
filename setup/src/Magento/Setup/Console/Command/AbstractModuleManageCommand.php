<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Code\GeneratedFiles;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\Status;

abstract class AbstractModuleManageCommand extends AbstractModuleCommand
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_ALL = 'all';
    const INPUT_KEY_FORCE = 'force';

    /**
     * @var GeneratedFiles
     */
    protected $generatedFiles;

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption(
            self::INPUT_KEY_FORCE,
            'f',
            InputOption::VALUE_NONE,
            'Bypass dependencies check'
        );
        $this->addOption(
            self::INPUT_KEY_ALL,
            null,
            InputOption::VALUE_NONE,
            ($this->isEnable() ? 'Enable' : 'Disable') . ' all modules'
        );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function isModuleRequired()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isEnable = $this->isEnable();
        if ($input->getOption(self::INPUT_KEY_ALL)) {
            /** @var \Magento\Framework\Module\FullModuleList $fullModulesList */
            $fullModulesList = $this->objectManager->get(\Magento\Framework\Module\FullModuleList::class);
            $modules = $fullModulesList->getNames();
        } else {
            $modules = $input->getArgument(self::INPUT_KEY_MODULES);
        }
        $messages = $this->validate($modules);
        if (!empty($messages)) {
            $output->writeln(implode(PHP_EOL, $messages));
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        try {
            $modulesToChange = $this->getStatus()->getModulesToChange($isEnable, $modules);
        } catch (\LogicException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        if (!empty($modulesToChange)) {
            $force = $input->getOption(self::INPUT_KEY_FORCE);
            if (!$force) {
                $constraints = $this->getStatus()->checkConstraints($isEnable, $modulesToChange);
                if ($constraints) {
                    $output->writeln(
                        "<error>Unable to change status of modules because of the following constraints:</error>"
                    );
                    $output->writeln('<error>' . implode("</error>\n<error>", $constraints) . '</error>');
                    // we must have an exit code higher than zero to indicate something was wrong
                    return \Magento\Framework\Console\Cli::RETURN_FAILURE;
                }
            }
            $this->setIsEnabled($isEnable, $modulesToChange, $output);
            $this->cleanup($input, $output);
            $this->getGeneratedFiles()->requestRegeneration();
            if ($force) {
                $output->writeln(
                    '<error>Alert: You used the --force option.'
                    . ' As a result, modules might not function properly.</error>'
                );
            }
        } else {
            $output->writeln('<info>No modules were changed.</info>');
        }
    }

    /**
     * Enable/disable modules
     *
     * @param bool $isEnable
     * @param string[] $modulesToChange
     * @param OutputInterface $output
     * @return void
     */
    private function setIsEnabled($isEnable, $modulesToChange, $output)
    {
        $this->getStatus()->setIsEnabled($isEnable, $modulesToChange);
        if ($isEnable) {
            $output->writeln('<info>The following modules have been enabled:</info>');
            $output->writeln('<info>- ' . implode("\n- ", $modulesToChange) . '</info>');
            $output->writeln('');
            if ($this->getDeploymentConfig()->isAvailable()) {
                $output->writeln(
                    '<info>To make sure that the enabled modules are properly registered,'
                    . " run 'setup:upgrade'.</info>"
                );
            }
        } else {
            $output->writeln('<info>The following modules have been disabled:</info>');
            $output->writeln('<info>- ' . implode("\n- ", $modulesToChange) . '</info>');
            $output->writeln('');
        }
    }

    /**
     * Get module status
     *
     * @return Status
     */
    private function getStatus()
    {
        return $this->objectManager->get(Status::class);
    }

    /**
     * Validate list of modules and return error messages
     *
     * @param string[] $modules
     * @return string[]
     */
    protected function validate(array $modules)
    {
        $messages = [];
        if (empty($modules)) {
            $messages[] = '<error>No modules specified. Specify a space-separated list of modules' .
                ' or use the --all option</error>';
        }
        return $messages;
    }

    /**
     * Is it "enable" or "disable" command
     *
     * @return bool
     */
    abstract protected function isEnable();

    /**
     * Get deployment config
     *
     * @return DeploymentConfig
     * @deprecated
     */
    private function getDeploymentConfig()
    {
        if (!($this->deploymentConfig instanceof DeploymentConfig)) {
            return $this->objectManager->get(DeploymentConfig::class);
        }
        return $this->deploymentConfig;
    }

    /**
     * Get deployment config
     *
     * @return GeneratedFiles
     * @deprecated
     */
    private function getGeneratedFiles()
    {
        if (!($this->generatedFiles instanceof GeneratedFiles)) {
            return $this->objectManager->get(GeneratedFiles::class);
        }
        return $this->generatedFiles;
    }
}
