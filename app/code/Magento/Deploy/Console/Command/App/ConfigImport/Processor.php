<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\ConfigImport;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\RuntimeException;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Framework\Exception\ValidatorException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Deploy\Model\DeploymentConfig\ImporterFactory;
use Magento\Framework\Console\QuestionPerformer\YesNo;
use Psr\Log\LoggerInterface as Logger;

/**
 * Runs process of importing config data from deployment configuration files.
 */
class Processor
{
    /**
     * Configuration data changes detector.
     *
     * @var ChangeDetector
     */
    private $changeDetector;

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
     * @var YesNo
     */
    private $questionPerformer;

    /**
     * @param ChangeDetector $changeDetector configuration data changes detector
     * @param ImporterPool $configImporterPool the pool of all deployment configuration importers
     * @param ImporterFactory $importerFactory the factory for creation of importer instance
     * @param DeploymentConfig $deploymentConfig the application deployment configuration
     * @param Hash $configHash the hash updater of config data
     * @param Logger $logger the logger
     * @param YesNo $questionPerformer The question performer for cli command
     */
    public function __construct(
        ChangeDetector $changeDetector,
        ImporterPool $configImporterPool,
        ImporterFactory $importerFactory,
        DeploymentConfig $deploymentConfig,
        Hash $configHash,
        Logger $logger,
        YesNo $questionPerformer
    ) {
        $this->changeDetector = $changeDetector;
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
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $importers = $this->configImporterPool->getImporters();
            if (!$importers || !$this->changeDetector->hasChanges()) {
                $output->writeln('<info>Nothing to import.</info>');
                return;
            }

            $output->writeln('<info>Processing configurations data from configuration file...</info>');

            /**
             * @var string $section
             * @var string $importerClassName
             */
            foreach ($importers as $section => $importerClassName) {
                if (!$this->changeDetector->hasChanges($section)) {
                    continue;
                }

                $data = (array)$this->deploymentConfig->getConfigData($section);
                $this->validateSectionData($section, $data);

                /** @var ImporterInterface $importer */
                $importer = $this->importerFactory->create($importerClassName);
                $warnings = $importer->getWarningMessages($data);
                $questions = array_merge($warnings, ['Do you want to continue [yes/no]?']);

                /**
                 * The importer return some warning questions which are contained in variable $warnings.
                 * A user should confirm import continuing if $warnings is not empty.
                 */
                if (!empty($warnings) && !$this->questionPerformer->execute($questions, $input, $output)) {
                    continue;
                }

                $messages = $importer->import($data);
                $output->writeln($messages);
                $this->configHash->regenerate($section);
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception);
            throw new RuntimeException(__('Import failed: %1', $exception->getMessage()), $exception);
        }
    }

    /**
     * Validates that current section has valid import data
     *
     * @param string $section Name of configuration section
     * @param array $data Configuration data for given section
     * @return void
     * @throws ValidatorException If current section has wrong data
     */
    private function validateSectionData($section, array $data)
    {
        $validator = $this->configImporterPool->getValidator($section);
        if (null !== $validator) {
            $messages = $validator->validate($data);
            if (!empty($messages)) {
                throw new ValidatorException(__(implode(PHP_EOL, $messages)));
            }
        }
    }
}
