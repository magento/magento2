<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\DeploymentConfig\ConfigHashManager;
use Magento\Framework\App\DeploymentConfig\ConfigImporterPool;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Console\Cli;

/**
 * Imports data from deployment configuration files to the DB.
 */
class ConfigImportCommand extends Command
{
    /**
     * Command name.
     */
    const COMMAND_NAME = 'app:config:import';

    /**
     * The manager of deployment configuration hash.
     *
     * @var ConfigHashManager
     */
    private $configHashManager;

    /**
     * Pool of all deployment configuration importers.
     *
     * @var ConfigImporterPool
     */
    private $configImporterPool;

    /**
     * Application deployment configuration.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param ConfigHashManager $configHashManager the manager of deployment configuration hash
     * @param ConfigImporterPool $configImporterPool the pool of all deployment configuration importers
     * @param DeploymentConfig $deploymentConfig the application deployment configuration
     */
    public function __construct(
        ConfigHashManager $configHashManager,
        ConfigImporterPool $configImporterPool,
        DeploymentConfig $deploymentConfig
    ) {
        $this->configHashManager = $configHashManager;
        $this->configImporterPool = $configImporterPool;
        $this->deploymentConfig = $deploymentConfig;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Import data from shared configuration files to appropriate data storage');

        parent::configure();
    }

    /**
     * Imports data from deployment configuration files to the DB.
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln('<info>Start import:</info>');

            $importers = $this->configImporterPool->getImporters();

            if (!$importers || $this->configHashManager->isHashValid()) {
                $output->writeln('<info>Nothing to import</info>');
            } else {
                /**
                 * @var string $namespace
                 * @var ImporterInterface $importer
                 */
                foreach ($importers as $namespace => $importer) {
                    $messages = $importer->import($this->deploymentConfig->getConfigData($namespace));
                    $output->writeln($messages);
                }

                $this->configHashManager->generateHash();
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
