<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Runs importing of config data from deployment configuration files.
 */
class Importer
{
    /**
     * The manager of deployment configuration hash.
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
     * @return array
     * @throws LocalizedException
     */
    public function import()
    {
        $messages = ['Start import:'];

        try {
            $importers = $this->configImporterPool->getImporters();

            if (!$importers || $this->configValidator->isValid()) {
                $messages[] = 'Nothing to import';
            } else {
                /**
                 * @var string $namespace
                 * @var ImporterInterface $importer
                 */
                foreach ($importers as $namespace => $importer) {
                    $messages = array_merge(
                        $messages,
                        $importer->import($this->deploymentConfig->getConfigData($namespace))
                    );
                }

                $this->configHash->regenerate();
            }
        } catch (LocalizedException $exception) {
            $this->logger->error($exception);
            throw new LocalizedException(__('Import is failed'), $exception);
        }

        return $messages;
    }
}
