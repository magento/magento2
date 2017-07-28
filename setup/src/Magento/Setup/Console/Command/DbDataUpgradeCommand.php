<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Model\InstallerFactory;
use Magento\Framework\Setup\ConsoleLogger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for install and update of data in DB
 * @since 2.0.0
 */
class DbDataUpgradeCommand extends AbstractSetupCommand
{
    /**
     * Factory to create installer
     *
     * @var InstallerFactory
     * @since 2.0.0
     */
    private $installFactory;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     * @since 2.0.0
     */
    private $deploymentConfig;

    /**
     * Inject dependencies
     *
     * @param InstallerFactory $installFactory
     * @param DeploymentConfig $deploymentConfig
     * @since 2.0.0
     */
    public function __construct(InstallerFactory $installFactory, DeploymentConfig $deploymentConfig)
    {
        $this->installFactory = $installFactory;
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('setup:db-data:upgrade')->setDescription('Installs and upgrades data in the DB');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln("<info>No information is available: the Magento application is not installed.</info>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        $installer = $this->installFactory->create(new ConsoleLogger($output));
        $installer->installDataFixtures();
    }
}
