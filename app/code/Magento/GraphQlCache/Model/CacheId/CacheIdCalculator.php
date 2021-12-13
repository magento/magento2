<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQlCache\Model\CacheId;

use Exception;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Math\Random;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Generator for the X-Magento-Cache-Id header value used as a cache key
 */
class CacheIdCalculator
{
    public const CACHE_ID_HEADER = 'X-Magento-Cache-Id';

    /**
     * Path to the salt value in the deployment config
     */
    public const SALT_CONFIG_PATH = 'cache/graphql/id_salt';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContextFactoryInterface
     */
    private $contextFactory;

    /**
     * @var Writer
     */
    private $envWriter;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var CacheIdFactorProviderInterface[]
     */
    private $idFactorProviders;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param LoggerInterface $logger
     * @param ContextFactoryInterface $contextFactory
     * @param DeploymentConfig $deploymentConfig
     * @param Writer $envWriter
     * @param Random $random
     * @param CacheIdFactorProviderInterface[] $idFactorProviders
     */
    public function __construct(
        LoggerInterface $logger,
        ContextFactoryInterface $contextFactory,
        DeploymentConfig $deploymentConfig,
        Writer $envWriter,
        Random $random,
        array $idFactorProviders = []
    ) {
        $this->logger = $logger;
        $this->contextFactory = $contextFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->envWriter = $envWriter;
        $this->random = $random;
        $this->idFactorProviders = $idFactorProviders;
    }

    /**
     * Calculates the value of X-Magento-Cache-Id
     *
     * @return string|null
     */
    public function getCacheId(): ?string
    {
        if (!$this->idFactorProviders) {
            return null;
        }

        try {
            $salt = $this->getSalt();
            $context = $this->contextFactory->get();
            foreach ($this->idFactorProviders as $idFactorProvider) {
                $keys[$idFactorProvider->getFactorName()] = $idFactorProvider->getFactorValue($context);
            }

            ksort($keys);
            $keysString = strtoupper(implode('|', array_values($keys))) . "|$salt";
            return hash('sha256', $keysString);
        } catch (Exception $e) {
            $this->logger->warning("Unable to obtain " . self::CACHE_ID_HEADER . " value: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets the existing salt from the environment config or creates one if it hasn't been set yet
     *
     * @return string
     * @throws Exception
     */
    private function getSalt(): string
    {
        $salt = $this->deploymentConfig->get(self::SALT_CONFIG_PATH);
        if ($salt) {
            return $salt;
        }

        $salt = $this->random->getRandomString(ConfigOptionsListConstants::STORE_KEY_RANDOM_STRING_SIZE);
        $config = new ConfigData(ConfigFilePool::APP_ENV);
        $config->set(self::SALT_CONFIG_PATH, $salt);
        $this->envWriter->saveConfig([$config->getFileKey() => $config->getData()], false, null, [], true);
        return $salt;
    }
}
