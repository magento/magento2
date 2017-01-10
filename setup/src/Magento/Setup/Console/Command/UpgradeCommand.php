<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Setup\ConsoleLogger;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for updating installed application after the code base has changed
 */
class UpgradeCommand extends AbstractSetupCommand
{
    /**
     * Option to skip deletion of var/generation directory
     */
    const INPUT_KEY_KEEP_GENERATED = 'keep-generated';

    /**
     * Installer service factory
     *
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     */
    public function __construct(InstallerFactory $installerFactory)
    {
        $this->installerFactory = $installerFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_KEEP_GENERATED,
                null,
                InputOption::VALUE_NONE,
                'Prevents generated files from being deleted. ' . PHP_EOL .
                'We discourage using this option except when deploying to production. ' . PHP_EOL .
                'Consult your system integrator or administrator for more information.'
            )
        ];
        $this->setName('setup:upgrade')
            ->setDescription('Upgrades the Magento application, DB data, and schema')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $keepGenerated = $input->getOption(self::INPUT_KEY_KEEP_GENERATED);
        $installer = $this->installerFactory->create(new ConsoleLogger($output));
        $installer->updateModulesSequence($keepGenerated);
        $installer->installSchema();
        $installer->installDataFixtures();
        if (!$keepGenerated) {
            $output->writeln('<info>Please re-run Magento compile command. Use the command "setup:di:compile"</info>');
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
