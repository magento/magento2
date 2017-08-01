<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model\Backend\Config;

/**
 * User backend observer helper class
 * @since 2.0.0
 */
class ObserverConfig
{
    /**
     * Backend configuration interface
     *
     * @var \Magento\Backend\App\ConfigInterface
     * @since 2.0.0
     */
    protected $backendConfig;

    /**
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function _isLatestPasswordExpired($latestPassword)
    {
        if (!isset($latestPassword['last_updated']) || $this->getAdminPasswordLifetime() == 0) {
            return false;
        }

        return (int)$latestPassword['last_updated'] + $this->getAdminPasswordLifetime() < time();
    }

    /**
     * Get admin lock threshold from configuration
     * @return int
     * @since 2.0.0
     */
    public function getAdminLockThreshold()
    {
        return 60 * (int)$this->backendConfig->getValue('admin/security/lockout_threshold');
    }

    /**
     * Check whether password change is forced
     *
     * @return bool
     * @since 2.0.0
     */
    public function isPasswordChangeForced()
    {
        return (bool)(int)$this->backendConfig->getValue('admin/security/password_is_forced');
    }

    /**
     * Get admin password lifetime
     *
     * @return int
     * @since 2.0.0
     */
    public function getAdminPasswordLifetime()
    {
        return 86400 * (int)$this->backendConfig->getValue('admin/security/password_lifetime');
    }

    /**
     * Get admin maxiumum security failures from config
     *
     * @return int
     * @since 2.0.0
     */
    public function getMaxFailures()
    {
        return (int)$this->backendConfig->getValue('admin/security/lockout_failures');
    }
}
