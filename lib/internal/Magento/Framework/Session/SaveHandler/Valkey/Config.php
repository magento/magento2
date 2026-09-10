<?php

namespace Magento\Framework\Session\SaveHandler\Valkey;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Session\SaveHandler\Redis\Config as RedisConfig;
use Magento\Setup\Model\ConfigOptionsList\Session as SessionConfig;

/**
 * Valkey session save handler
 */
class Config extends RedisConfig
{
    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
        private readonly State $appState,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($deploymentConfig, $appState, $scopeConfig);
    }

    /**
     * @inheritdoc
     */
    public function getLogLevel()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_LOG_LEVEL) ?? parent::getLogLevel();
    }

    /**
     * @inheritdoc
     */
    public function getHost()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_HOST) ?? parent::getHost();
    }

    /**
     * @inheritdoc
     */
    public function getPort()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_PORT) ?? parent::getPort();
    }

    /**
     * @inheritdoc
     */
    public function getDatabase()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_DATABASE) ?? parent::getDatabase();
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_PASSWORD) ?? parent::getPassword();
    }

    /**
     * @inheritdoc
     */
    public function getTimeout()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_TIMEOUT) ?? parent::getTimeout();
    }

    /**
     * @inheritdoc
     */
    public function getRetries()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_RETRIES) ?? parent::getRetries();
    }

    /**
     * @inheritdoc
     */
    public function getPersistentIdentifier()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_PERSISTENT_IDENTIFIER) ?? parent::getPersistentIdentifier();
    }

    /**
     * @inheritdoc
     */
    public function getCompressionThreshold()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_COMPRESSION_THRESHOLD) ?? parent::getCompressionThreshold();
    }

    /**
     * @inheritdoc
     */
    public function getCompressionLibrary()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_COMPRESSION_LIBRARY) ?? parent::getCompressionLibrary();
    }

    /**
     * @inheritdoc
     */
    public function getMaxConcurrency()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_MAX_CONCURRENCY) ?? parent::getMaxConcurrency();
    }

    /**
     * @inheritdoc
     */
    public function getMinLifetime()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_MIN_LIFETIME) ?? parent::getMinLifetime();
    }

    /**
     * @inheritdoc
     */
    public function getDisableLocking()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_DISABLE_LOCKING) ?? parent::getDisableLocking();
    }

    /**
     * @inheritdoc
     */
    public function getBotLifetime()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_BOT_LIFETIME) ?? parent::getBotLifetime();
    }

    /**
     * @inheritdoc
     */
    public function getBotFirstLifetime()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_BOT_FIRST_LIFETIME) ?? parent::getBotFirstLifetime();
    }

    /**
     * @inheritdoc
     */
    public function getFirstLifetime()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_FIRST_LIFETIME) ?? parent::getFirstLifetime();
    }

    /**
     * @inheritdoc
     */
    public function getBreakAfter()
    {
        return $this->deploymentConfig->get('session/valkey/break_after_' . $this->appState->getAreaCode()) ?? parent::getBreakAfter();
    }

    /**
     * @inheritdoc
     */
    public function getSentinelServers()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_SENTINEL_SERVERS) ?? parent::getSentinelServers();
    }

    /**
     * @inheritdoc
     */
    public function getSentinelMaster()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_SENTINEL_MASTER) ?? parent::getSentinelMaster();
    }

    /**
     * @inheritdoc
     */
    public function getSentinelVerifyMaster()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_SENTINEL_VERIFY_MASTER) ?? parent::getSentinelVerifyMaster();
    }

    /**
     * @inheritdoc
     */
    public function getSentinelConnectRetries()
    {
        return $this->deploymentConfig->get(SessionConfig::CONFIG_PATH_SESSION_VALKEY_SENTINEL_CONNECT_RETRIES) ?? parent::getSentinelConnectRetries();
    }
}
