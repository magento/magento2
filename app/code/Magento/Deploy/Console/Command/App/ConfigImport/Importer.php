<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\ConfigImport;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface as Logger;
use Magento\Deploy\Model\DeploymentConfig\Validator;
use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs importing of config data from deployment configuration files.
 */
class Importer
{
    /**
     * The configuration data validator.
     *
     * @var Validator
     */
    private $configValidator;

    /**
     * Pool of all deployment configuration importers.
     *
     * @var ImporterPool
     */
    private $configImporterPool;

    /**
     * Application deployment configuration.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Hash updater of config data.
     *
     * @var Hash
     */
    private $configHash;

    /**
     * Logger.
     *
     * @var Logger
     */
    private $logger;

    /**
     * @param Validator $configValidator the manager of deployment configuration hash
     * @param ImporterPool $configImporterPool the pool of all deployment configuration importers
     * @param DeploymentConfig $deploymentConfig the application deployment configuration
     * @param Hash $configHash the hash updater of config data
     * @param Logger $logger the logger
     */
    public function __construct(
        Validator $configValidator,
        ImporterPool $configImporterPool,
        DeploymentConfig $deploymentConfig,
        Hash $configHash,
        Logger $logger
    ) {
        $this->configValidator = $configValidator;
        $this->configImporterPool = $configImporterPool;
        $this->deploymentConfig = $deploymentConfig;
        $this->configHash = $configHash;
        $this->logger = $logger;
    }

    /**
     * Runs importing of config data from deployment configuration files.
     *
     * @param OutputInterface $output the CLI output
     * @return void
     * @throws LocalizedException
     */
    public function import(OutputInterface $output)
    {
        $output->writeln('<info>Start import:</info>');

        try {
            $importers = $this->configImporterPool->getImporters();

            if (!$importers || $this->configValidator->isValid()) {
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

                $this->configHash->regenerate();
            }
        } catch (LocalizedException $exception) {
            $this->logger->error($exception);
            throw new LocalizedException(__('Import is failed'), $exception);
        }
    }
}
