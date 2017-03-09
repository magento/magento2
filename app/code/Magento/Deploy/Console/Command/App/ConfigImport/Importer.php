<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\ConfigImport;

use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\RuntimeException;
use Psr\Log\LoggerInterface as Logger;
use Magento\Deploy\Model\DeploymentConfig\Validator;
use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Deploy\Model\DeploymentConfig\ImporterFactory;

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
     * Factory for creation of importer instance.
     *
     * @var ImporterFactory
     */
    private $importerFactory;

    /**
     * Logger.
     *
     * @var Logger
     */
    private $logger;

    /**
     * Asks questions in interactive mode of cli commands.
     *
     * @var QuestionPerformer
     */
    private $questionPerformer;

    /**
     * @param Validator $configValidator the manager of deployment configuration hash
     * @param ImporterPool $configImporterPool the pool of all deployment configuration importers
     * @param ImporterFactory $importerFactory the factory for creation of importer instance
     * @param DeploymentConfig $deploymentConfig the application deployment configuration
     * @param Hash $configHash the hash updater of config data
     * @param Logger $logger the logger
     * @param QuestionPerformer $questionPerformer The question performer for cli command
     */
    public function __construct(
        Validator $configValidator,
        ImporterPool $configImporterPool,
        ImporterFactory $importerFactory,
        DeploymentConfig $deploymentConfig,
        Hash $configHash,
        Logger $logger,
        QuestionPerformer $questionPerformer
    ) {
        $this->configValidator = $configValidator;
        $this->configImporterPool = $configImporterPool;
        $this->importerFactory = $importerFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->configHash = $configHash;
        $this->logger = $logger;
        $this->questionPerformer = $questionPerformer;
    }

    /**
     * Runs importing of config data from deployment configuration files.
     *
     * @param InputInterface $input The CLI input
     * @param OutputInterface $output The CLI output
     * @return void
     * @throws RuntimeException is thrown when import has failed
     */
    public function import(InputInterface $input, OutputInterface $output)
    {
        try {
            $importers = $this->configImporterPool->getImporters();
            if (!$importers || $this->configValidator->isValid()) {
                $output->writeln('<info>Nothing to import.</info>');
                return;
            } else {
                $output->writeln('<info>Start import:</info>');
            }

            /**
             * @var string $section
             * @var string $importerClassName
             */
            foreach ($importers as $section => $importerClassName) {
                if ($this->configValidator->isValid($section)) {
                    continue;
                }

                /** @var ImporterInterface $importer */
                $importer = $this->importerFactory->create($importerClassName);

                $configData = (array)$this->deploymentConfig->getConfigData($section);
                if (
                    !$input->getOption(ConfigImportCommand::INPUT_OPTION_FORCE)
                    && !empty($warnings = $importer->getWarningMessages($configData))
                    && !$this->questionPerformer->execute($warnings, $input, $output)
                ) {
                    continue;
                }

                $messages = $importer->import($configData);
                $output->writeln($messages);
                $this->configHash->regenerate($section);
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception);
            throw new RuntimeException(__('Import is failed: %1', $exception->getMessage()), $exception);
        }
    }
}
