<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractModuleManageCommand extends AbstractModuleCommand
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_ALL = 'all';
    const INPUT_KEY_FORCE = 'force';

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
            $fullModulesList = $this->objectManager->get('Magento\Framework\Module\FullModuleList');
            $modules = $fullModulesList->getNames();
        } else {
            $modules = $input->getArgument(self::INPUT_KEY_MODULES);
        }
        $messages = $this->validate($modules);
        if (!empty($messages)) {
            $output->writeln(implode(PHP_EOL, $messages));
            return;
        }
        /**
         * @var \Magento\Framework\Module\Status $status
         */
        $status = $this->objectManager->get('Magento\Framework\Module\Status');
        try {
            $modulesToChange = $status->getModulesToChange($isEnable, $modules);
        } catch (\LogicException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return;
        }
        if (!empty($modulesToChange)) {
            $force = $input->getOption(self::INPUT_KEY_FORCE);
            if (!$force) {
                $constraints = $status->checkConstraints($isEnable, $modulesToChange);
                if ($constraints) {
                    $output->writeln(
                        "<error>Unable to change status of modules because of the following constraints:</error>"
                    );
                    $output->writeln('<error>' . implode("</error>\n<error>", $constraints) . '</error>');
                    return;
                }
            }
            $status->setIsEnabled($isEnable, $modulesToChange);
            if ($isEnable) {
                $output->writeln('<info>The following modules have been enabled:</info>');
                $output->writeln('<info>- ' . implode("\n- ", $modulesToChange) . '</info>');
                $output->writeln('');
                $output->writeln(
                    '<info>To make sure that the enabled modules are properly registered,'
                    . " run 'setup:upgrade'.</info>"
                );
            } else {
                $output->writeln('<info>The following modules have been disabled:</info>');
                $output->writeln('<info>- ' . implode("\n- ", $modulesToChange) . '</info>');
                $output->writeln('');
            }
            $this->cleanup($input, $output);
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
}
