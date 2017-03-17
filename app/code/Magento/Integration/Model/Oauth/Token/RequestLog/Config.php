<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth\Token\RequestLog;

use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * Token request log config.
 */
class Config
{
    /**
     * @var ReinitableConfigInterface
     */
    private $storeConfig;

    /**
     * Initialize dependencies.
     *
     * @param ReinitableConfigInterface $storeConfig
     */
    public function __construct(ReinitableConfigInterface $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    /**
     * Get maximum allowed authentication failures count before account is locked.
     *
     * @return int
     */
    public function getMaxFailuresCount()
    {
        return (int)$this->storeConfig->getValue('oauth/authentication_lock/max_failures_count');
    }

    /**
     * Get period of time in seconds after which account will be unlocked.
     *
     * @return int
     */
    public function getLockTimeout()
    {
        return (int)$this->storeConfig->getValue('oauth/authentication_lock/timeout');
    }
}
