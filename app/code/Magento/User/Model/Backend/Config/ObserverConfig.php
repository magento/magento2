<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model\Backend\Config;

/**
 * User backend observer helper class
 */
class ObserverConfig
{
    /**
     * Backend configuration interface
     *
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $backendConfig;

    /**
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     */
    public function __construct(
        \Magento\Backend\App\ConfigInterface $backendConfig
    ) {
        $this->backendConfig = $backendConfig;
    }

    /**
     * Check if latest password is expired
     *
     * @param array $latestPassword
     * @return bool
     */
    public function _isLatestPasswordExpired($latestPassword)
    {
        if (!isset($latestPassword['expires']) || $this->getAdminPasswordLifetime() == 0) {
            return false;
        } else {
            return (int)$latestPassword['expires'] < time();
        }
    }

    /**
     * Get admin lock threshold from configuration
     * @return int
     */
    public function getAdminLockThreshold()
    {
        return 60 * (int)$this->backendConfig->getValue('admin/security/lockout_threshold');
    }

    /**
     * Check whether password change is forced
     *
     * @return bool
     */
    public function isPasswordChangeForced()
    {
        return (bool)(int)$this->backendConfig->getValue('admin/security/password_is_forced');
    }

    /**
     * Get admin password lifetime
     *
     * @return int
     */
    public function getAdminPasswordLifetime()
    {
        return 86400 * (int)$this->backendConfig->getValue('admin/security/password_lifetime');
    }

    /**
     * Get admin maxiumum security failures from config
     *
     * @return int
     */
    public function getMaxFailures()
    {
        return (int)$this->backendConfig->getValue('admin/security/lockout_failures');
    }
}
