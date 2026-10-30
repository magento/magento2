<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Deploy\Model\Plugin;

use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Deploy\Model\DeploymentConfig\Hash\Generator as HashGenerator;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * This is plugin for Magento\Framework\App\FrontController class.
 *
 * Detects that the configuration data from the deployment configuration files has been changed.
 * If config data was changed throws LocalizedException because we should stop work of Magento and then import
 * config data from shared configuration files into appropriate application sources.
 */
class ConfigChangeDetector
{
    private const DEPLOYMENT_BLUE_GREEN_ENABLED = 'deployment/blue_green/enabled';

    /**
     * Configuration data changes detector.
     *
     * @var ChangeDetector
     */
    private $changeDetector;

    /** @var DeploymentConfig  */
    private $deploymentConfig;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var DataCollector
     */
    private DataCollector $dataCollector;

    /**
     * @var HashGenerator
     */
    private HashGenerator $hashGenerator;

    /**
     * @param ChangeDetector $changeDetector
     * @param DeploymentConfig $deploymentConfig
     * @param CacheInterface|null $cache
     * @param DataCollector|null $dataCollector
     * @param HashGenerator|null $hashGenerator
     */
    public function __construct(
        ChangeDetector $changeDetector,
        DeploymentConfig $deploymentConfig,
        ?CacheInterface $cache = null,
        ?DataCollector $dataCollector = null,
        ?HashGenerator $hashGenerator = null
    ) {
        $this->changeDetector = $changeDetector;
        $this->deploymentConfig = $deploymentConfig;
        $this->cache = $cache ?? ObjectManager::getInstance()->get(CacheInterface::class);
        $this->dataCollector = $dataCollector ?? ObjectManager::getInstance()->get(DataCollector::class);
        $this->hashGenerator = $hashGenerator ?? ObjectManager::getInstance()->get(HashGenerator::class);
    }

    /**
     * Performs detects that config data from deployment configuration files been changed.
     *
     * @param FrontControllerInterface $subject the interface of frontend controller is wrapped by this plugin
     * @param RequestInterface $request the object that contains request params
     * @return void
     * @throws LocalizedException is thrown if config data from deployment configuration files is not valid
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(FrontControllerInterface $subject, RequestInterface $request)
    {
        if ($this->deploymentConfig->get(self::DEPLOYMENT_BLUE_GREEN_ENABLED)) {
            return;
        }

        $cacheKey = $this->getCacheKey();
        if ($this->cache->load($cacheKey)) {
            return;
        }

        if ($this->changeDetector->hasChanges()) {
            throw new LocalizedException(
                __(
                    'The configuration file has changed.'
                    . ' Run the "app:config:import" or the "setup:upgrade" command to synchronize the configuration.'
                )
            );
        }

        $this->cache->save('true', $cacheKey, [Hash::CACHE_TAG]);
    }

    /**
     * Build a cache key from the current configuration section hashes.
     *
     * @return string
     */
    private function getCacheKey(): string
    {
        $hashes = [];
        foreach ($this->dataCollector->getConfig() as $section => $config) {
            $hashes[$section] = $config === null ? null : $this->hashGenerator->generate($config);
        }

        return 'deployment_config_hash_in_sync_' . $this->hashGenerator->generate($hashes);
    }
}
