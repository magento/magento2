<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\SaveHandler\Redis;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * Redis session save handler
 */
class Config implements \Cm\RedisSession\Handler\ConfigInterface
{
    /**
     * Configuration path for log level
     */
    public const PARAM_LOG_LEVEL               = 'session/redis/log_level';

    /**
     * Configuration path for host
     */
    public const PARAM_HOST                    = 'session/redis/host';

    /**
     * Configuration path for port
     */
    public const PARAM_PORT                    = 'session/redis/port';

    /**
     * Configuration path for database
     */
    public const PARAM_DATABASE                = 'session/redis/database';

    /**
     * Configuration path for password
     */
    public const PARAM_PASSWORD                = 'session/redis/password';

    /**
     * Configuration path for connection timeout
     */
    public const PARAM_TIMEOUT                 = 'session/redis/timeout';

    /**
     * Configuration path for number of connection retries
     */
    public const PARAM_RETRIES = 'session/redis/retries';

    /**
     * Configuration path for persistent identifier
     */
    public const PARAM_PERSISTENT_IDENTIFIER   = 'session/redis/persistent_identifier';

    /**
     * Configuration path for compression threshold
     */
    public const PARAM_COMPRESSION_THRESHOLD   = 'session/redis/compression_threshold';

    /**
     * Configuration path for compression library
     */
    public const PARAM_COMPRESSION_LIBRARY     = 'session/redis/compression_library';

    /**
     * Configuration path for maximum number of processes that can wait for a lock on one session
     */
    public const PARAM_MAX_CONCURRENCY         = 'session/redis/max_concurrency';

    /**
     * Configuration path for minimum session lifetime
     */
    public const PARAM_MAX_LIFETIME            = 'session/redis/max_lifetime';

    /**
     * Configuration path for min
     */
    public const PARAM_MIN_LIFETIME            = 'session/redis/min_lifetime';

    /**
     * Configuration path for disabling session locking entirely flag
     */
    public const PARAM_DISABLE_LOCKING         = 'session/redis/disable_locking';

    /**
     * Configuration path for lifetime of session for bots on subsequent writes
     */
    public const PARAM_BOT_LIFETIME            = 'session/redis/bot_lifetime';

    /**
     * Configuration path for lifetime of session for bots on the first write
     */
    public const PARAM_BOT_FIRST_LIFETIME      = 'session/redis/bot_first_lifetime';

    /**
     * Configuration path for lifetime of session for non-bots on the first write
     */
    public const PARAM_FIRST_LIFETIME          = 'session/redis/first_lifetime';

    /**
     * Configuration path for number of seconds to wait before trying to break the lock
     */
    public const PARAM_BREAK_AFTER             = 'session/redis/break_after';

    /**
     * Configuration path for comma separated list of sentinel servers
     */
    public const PARAM_SENTINEL_SERVERS        = 'session/redis/sentinel_servers';

    /**
     * Configuration path for sentinel master
     */
    public const PARAM_SENTINEL_MASTER         = 'session/redis/sentinel_master';

    /**
     * Configuration path for verify sentinel master flag
     */
    public const PARAM_SENTINEL_VERIFY_MASTER  = 'session/redis/sentinel_verify_master';

    /**
     * Configuration path for number of sentinel connection retries
     */
    public const PARAM_SENTINEL_CONNECT_RETRIES = 'session/redis/sentinel_connect_retries';

    /**
     * Cookie lifetime config path
     */
    public const XML_PATH_COOKIE_LIFETIME = 'web/cookie/cookie_lifetime';

    /**
     * Admin session lifetime config path
     */
    public const XML_PATH_ADMIN_SESSION_LIFETIME = 'admin/security/session_lifetime';

    /**
     * Session max lifetime
     */
    public const SESSION_MAX_LIFETIME = 31536000;

    /**
     * Try to break lock for at most this many seconds
     */
    public const DEFAULT_FAIL_AFTER = 15;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param State $appState
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        State $appState,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->appState = $appState;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getLogLevel()
    {
        return $this->deploymentConfig->get(self::PARAM_LOG_LEVEL);
    }

    /**
     * @inheritdoc
     */
    public function getHost()
    {
        return $this->deploymentConfig->get(self::PARAM_HOST);
    }

    /**
     * @inheritdoc
     */
    public function getPort()
    {
        return $this->deploymentConfig->get(self::PARAM_PORT);
    }

    /**
     * @inheritdoc
     */
    public function getDatabase()
    {
        return $this->deploymentConfig->get(self::PARAM_DATABASE);
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return $this->deploymentConfig->get(self::PARAM_PASSWORD);
    }

    /**
     * @inheritdoc
     */
    public function getTimeout()
    {
        return $this->deploymentConfig->get(self::PARAM_TIMEOUT);
    }

    /**
     * @inheritdoc
     */
    public function getRetries()
    {
        return $this->deploymentConfig->get(self::PARAM_RETRIES);
    }

    /**
     * @inheritdoc
     */
    public function getPersistentIdentifier()
    {
        return $this->deploymentConfig->get(self::PARAM_PERSISTENT_IDENTIFIER);
    }

    /**
     * @inheritdoc
     */
    public function getCompressionThreshold()
    {
        return $this->deploymentConfig->get(self::PARAM_COMPRESSION_THRESHOLD);
    }

    /**
     * @inheritdoc
     */
    public function getCompressionLibrary()
    {
        return $this->deploymentConfig->get(self::PARAM_COMPRESSION_LIBRARY);
    }

    /**
     * @inheritdoc
     */
    public function getMaxConcurrency()
    {
        return $this->deploymentConfig->get(self::PARAM_MAX_CONCURRENCY);
    }

    /**
     * @inheritdoc
     */
    public function getMaxLifetime()
    {
        return self::SESSION_MAX_LIFETIME;
    }

    /**
     * @inheritdoc
     */
    public function getMinLifetime()
    {
        return $this->deploymentConfig->get(self::PARAM_MIN_LIFETIME);
    }

    /**
     * @inheritdoc
     */
    public function getDisableLocking()
    {
        return $this->deploymentConfig->get(self::PARAM_DISABLE_LOCKING);
    }

    /**
     * @inheritdoc
     */
    public function getBotLifetime()
    {
        return $this->deploymentConfig->get(self::PARAM_BOT_LIFETIME);
    }

    /**
     * @inheritdoc
     */
    public function getBotFirstLifetime()
    {
        return $this->deploymentConfig->get(self::PARAM_BOT_FIRST_LIFETIME);
    }

    /**
     * @inheritdoc
     */
    public function getFirstLifetime()
    {
        return $this->deploymentConfig->get(self::PARAM_FIRST_LIFETIME);
    }

    /**
     * @inheritdoc
     */
    public function getBreakAfter()
    {
        return $this->deploymentConfig->get(self::PARAM_BREAK_AFTER . '_' . $this->appState->getAreaCode());
    }

    /**
     * @inheritdoc
     */
    public function getLifetime()
    {
        if ($this->appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return (int)$this->scopeConfig->getValue(self::XML_PATH_ADMIN_SESSION_LIFETIME);
        }
        return (int)$this->scopeConfig->getValue(self::XML_PATH_COOKIE_LIFETIME, StoreScopeInterface::SCOPE_STORE);
    }

    /**
     * @inheritdoc
     */
    public function getSentinelServers()
    {
        return $this->deploymentConfig->get(self::PARAM_SENTINEL_SERVERS);
    }

    /**
     * @inheritdoc
     */
    public function getSentinelMaster()
    {
        return $this->deploymentConfig->get(self::PARAM_SENTINEL_MASTER);
    }

    /**
     * @inheritdoc
     */
    public function getSentinelVerifyMaster()
    {
        return $this->deploymentConfig->get(self::PARAM_SENTINEL_VERIFY_MASTER);
    }

    /**
     * @inheritdoc
     */
    public function getSentinelConnectRetries()
    {
        return $this->deploymentConfig->get(self::PARAM_SENTINEL_CONNECT_RETRIES);
    }

    /**
     * @inheritdoc
     */
    public function getFailAfter()
    {
        return self::DEFAULT_FAIL_AFTER;
    }
}
