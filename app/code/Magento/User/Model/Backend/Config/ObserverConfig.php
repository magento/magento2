<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\User\Model\Backend\Config;

/**
 * User backend observer helper class
 *
 * Class \Magento\User\Model\Backend\Config\ObserverConfig
 */
class ObserverConfig
{
    /**
     * Config path for lockout threshold
     */
    private const XML_ADMIN_SECURITY_LOCKOUT_THRESHOLD = 'admin/security/lockout_threshold';

    /**
     * Config path for password change is forced or not
     */
    private const XML_ADMIN_SECURITY_PASSWORD_IS_FORCED = 'admin/security/password_is_forced';

    /**
     * Config path for password lifetime
     */
    private const XML_ADMIN_SECURITY_PASSWORD_LIFETIME = 'admin/security/password_lifetime';

    /**
     * Config path for maximum lockout failures
     */
    private const XML_ADMIN_SECURITY_LOCKOUT_FAILURES = 'admin/security/lockout_failures';

    /**
     * Backend configuration interface
     *
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $backendConfig;

    /**
     * Constructor
     *
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
        if (!isset($latestPassword['last_updated']) || $this->getAdminPasswordLifetime() == 0) {
            return false;
        }

        return (int)$latestPassword['last_updated'] + $this->getAdminPasswordLifetime() < time();
    }

    /**
     * Get admin lock threshold from configuration
     *
     * @return int
     */
    public function getAdminLockThreshold()
    {
        return 60 * (int)$this->backendConfig->getValue(self::XML_ADMIN_SECURITY_LOCKOUT_THRESHOLD);
    }

    /**
     * Check whether password change is forced
     *
     * @return bool
     */
    public function isPasswordChangeForced()
    {
        return (bool)(int)$this->backendConfig->getValue(self::XML_ADMIN_SECURITY_PASSWORD_IS_FORCED);
    }

    /**
     * Get admin password lifetime
     *
     * @return int
     */
    public function getAdminPasswordLifetime()
    {
        return 86400 * (int)$this->backendConfig->getValue(self::XML_ADMIN_SECURITY_PASSWORD_LIFETIME);
    }

    /**
     * Get admin maximum security failures from config
     *
     * @return int
     */
    public function getMaxFailures()
    {
        return (int)$this->backendConfig->getValue(self::XML_ADMIN_SECURITY_LOCKOUT_FAILURES);
    }
}
