<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth\Token\RequestLog;

use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * Token request log config.
 * @since 2.1.0
 */
class Config
{
    /**
     * @var ReinitableConfigInterface
     * @since 2.1.0
     */
    private $storeConfig;

    /**
     * Initialize dependencies.
     *
     * @param ReinitableConfigInterface $storeConfig
     * @since 2.1.0
     */
    public function __construct(ReinitableConfigInterface $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    /**
     * Get maximum allowed authentication failures count before account is locked.
     *
     * @return int
     * @since 2.1.0
     */
    public function getMaxFailuresCount()
    {
        return (int)$this->storeConfig->getValue('oauth/authentication_lock/max_failures_count');
    }

    /**
     * Get period of time in seconds after which account will be unlocked.
     *
     * @return int
     * @since 2.1.0
     */
    public function getLockTimeout()
    {
        return (int)$this->storeConfig->getValue('oauth/authentication_lock/timeout');
    }
}
