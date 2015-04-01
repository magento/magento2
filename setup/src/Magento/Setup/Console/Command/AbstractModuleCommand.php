<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractModuleCommand extends Command
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_MODULES = 'module';
    const INPUT_KEY_FORCE = 'force';

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
        $this->setDefinition([
                new InputArgument(
                    self::INPUT_KEY_MODULES,
                    InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                    'Name of the module'
                ),
                new InputOption(
                    self::INPUT_KEY_FORCE,
                    'f',
                    InputOption::VALUE_NONE,
                    'Bypass dependencies check'
                )
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isEnable = $this->isEnable();
        $modules = $input->getArgument(self::INPUT_KEY_MODULES);
        /**
         * @var \Magento\Framework\Module\Status $status
         */
        $status = $this->objectManagerProvider->get()->create('Magento\Framework\Module\Status');

        $modulesToChange = $status->getModulesToChange($isEnable, $modules);
        if (!empty($modulesToChange)) {
            $force = $input->getOption(self::INPUT_KEY_FORCE);
            if (!$force) {
                $constraints = $status->checkConstraints($isEnable, $modulesToChange);
                if ($constraints) {
                    $output->writeln(
                        "<error>Unable to change status of modules because of the following constraints:</error>"
                    );
                    $output->writeln('<error>' . implode("\n", $constraints) . '</error>');
                    return;
                }
            } else {
                $output->writeln('<error>Alert: Your store may not operate properly because of '
                    . "dependencies and conflicts of this module(s).</error>");
            }
            $status->setIsEnabled($isEnable, $modulesToChange);
            if ($isEnable) {
                $output->writeln('<info>The following modules have been enabled:</info>');
                $output->writeln('<info>' . implode(', ', $modulesToChange) . '</info>');
                $output->writeln('<info>To make sure that the enabled modules are properly registered,'
                    . " run 'update' command.</info>");
            } else {
                $output->writeln('<info>The following modules have been disabled:</info>');
                $output->writeln('<info>' . implode(', ', $modulesToChange) . '</info>');
            }
        } else {
            $output->writeln('<info>There have been no changes to any modules.</info>');
        }
    }

    abstract protected function isEnable();
}
