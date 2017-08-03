<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\SaveHandler\Redis;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;

/**
 * Redis session save handler
 * @since 2.0.6
 */
class Config implements \Cm\RedisSession\Handler\ConfigInterface
{
    /**
     * Configuration path for log level
     */
    const PARAM_LOG_LEVEL               = 'session/redis/log_level';

    /**
     * Configuration path for host
     */
    const PARAM_HOST                    = 'session/redis/host';

    /**
     * Configuration path for port
     */
    const PARAM_PORT                    = 'session/redis/port';

    /**
     * Configuration path for database
     */
    const PARAM_DATABASE                = 'session/redis/database';

    /**
     * Configuration path for password
     */
    const PARAM_PASSWORD                = 'session/redis/password';

    /**
     * Configuration path for connection timeout
     */
    const PARAM_TIMEOUT                 = 'session/redis/timeout';

    /**
     * Configuration path for persistent identifier
     */
    const PARAM_PERSISTENT_IDENTIFIER   = 'session/redis/persistent_identifier';

    /**
     * Configuration path for compression threshold
     */
    const PARAM_COMPRESSION_THRESHOLD   = 'session/redis/compression_threshold';

    /**
     * Configuration path for compression library
     */
    const PARAM_COMPRESSION_LIBRARY     = 'session/redis/compression_library';

    /**
     * Configuration path for maximum number of processes that can wait for a lock on one session
     */
    const PARAM_MAX_CONCURRENCY         = 'session/redis/max_concurrency';

    /**
     * Configuration path for minimum session lifetime
     */
    const PARAM_MAX_LIFETIME            = 'session/redis/max_lifetime';

    /**
     * Configuration path for min
     */
    const PARAM_MIN_LIFETIME            = 'session/redis/min_lifetime';

    /**
     * Configuration path for disabling session locking entirely flag
     */
    const PARAM_DISABLE_LOCKING         = 'session/redis/disable_locking';

    /**
     * Configuration path for lifetime of session for bots on subsequent writes
     */
    const PARAM_BOT_LIFETIME            = 'session/redis/bot_lifetime';

    /**
     * Configuration path for lifetime of session for bots on the first write
     */
    const PARAM_BOT_FIRST_LIFETIME      = 'session/redis/bot_first_lifetime';

    /**
     * Configuration path for lifetime of session for non-bots on the first write
     */
    const PARAM_FIRST_LIFETIME          = 'session/redis/first_lifetime';

    /**
     * Configuration path for number of seconds to wait before trying to break the lock
     */
    const PARAM_BREAK_AFTER             = 'session/redis/break_after';

    /**
     * Cookie lifetime config path
     */
    const XML_PATH_COOKIE_LIFETIME = 'web/cookie/cookie_lifetime';

    /**
     * Admin session lifetime config path
     */
    const XML_PATH_ADMIN_SESSION_LIFETIME = 'admin/security/session_lifetime';

    /**
     * Session max lifetime
     */
    const SESSION_MAX_LIFETIME = 31536000;

    /**
     * Deployment config
     *
     * @var DeploymentConfig $deploymentConfig
     * @since 2.0.6
     */
    private $deploymentConfig;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.6
     */
    private $scopeConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param State $appState
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.0.6
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
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getLogLevel()
    {
        return $this->deploymentConfig->get(self::PARAM_LOG_LEVEL);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getHost()
    {
        return $this->deploymentConfig->get(self::PARAM_HOST);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getPort()
    {
        return $this->deploymentConfig->get(self::PARAM_PORT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getDatabase()
    {
        return $this->deploymentConfig->get(self::PARAM_DATABASE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getPassword()
    {
        return $this->deploymentConfig->get(self::PARAM_PASSWORD);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getTimeout()
    {
        return $this->deploymentConfig->get(self::PARAM_TIMEOUT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getPersistentIdentifier()
    {
        return $this->deploymentConfig->get(self::PARAM_PERSISTENT_IDENTIFIER);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getCompressionThreshold()
    {
        return $this->deploymentConfig->get(self::PARAM_COMPRESSION_THRESHOLD);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getCompressionLibrary()
    {
        return $this->deploymentConfig->get(self::PARAM_COMPRESSION_LIBRARY);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getMaxConcurrency()
    {
        return $this->deploymentConfig->get(self::PARAM_MAX_CONCURRENCY);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getMaxLifetime()
    {
        return self::SESSION_MAX_LIFETIME;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getMinLifetime()
    {
        return $this->deploymentConfig->get(self::PARAM_MIN_LIFETIME);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getDisableLocking()
    {
        return $this->deploymentConfig->get(self::PARAM_DISABLE_LOCKING);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getBotLifetime()
    {
        return $this->deploymentConfig->get(self::PARAM_BOT_LIFETIME);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getBotFirstLifetime()
    {
        return $this->deploymentConfig->get(self::PARAM_BOT_FIRST_LIFETIME);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getFirstLifetime()
    {
        return $this->deploymentConfig->get(self::PARAM_FIRST_LIFETIME);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getBreakAfter()
    {
        return $this->deploymentConfig->get(self::PARAM_BREAK_AFTER . '_' . $this->appState->getAreaCode());
    }

    /**
     * {@inheritdoc}
     * @since 2.0.6
     */
    public function getLifetime()
    {
        if ($this->appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return (int)$this->scopeConfig->getValue(self::XML_PATH_ADMIN_SESSION_LIFETIME);
        }
        return (int)$this->scopeConfig->getValue(self::XML_PATH_COOKIE_LIFETIME, StoreScopeInterface::SCOPE_STORE);
    }
}
