<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

/**
 * Password security config Interface
 *
 * @api
 * @since 100.1.0
 */
interface ConfigInterface
{
    /**
     * Get customer service email address
     *
     * @return string
     * @since 100.1.0
     */
    public function getCustomerServiceEmail();

    /**
     * Get time period limitation of password reset requests
     *
     * @return int
     * @since 100.1.0
     */
    public function getLimitationTimePeriod();

    /**
     * Check if admin account sharing is enabled
     *
     * @return bool
     * @since 100.1.0
     */
    public function isAdminAccountSharingEnabled();

    /**
     * Get admin session lifetime
     *
     * @return int
     * @since 100.1.0
     */
    public function getAdminSessionLifetime();

    /**
     * Get password reset protection type
     *
     * @return int
     * @since 100.1.0
     */
    public function getPasswordResetProtectionType();

    /**
     * Get max number password reset requests per time period
     *
     * @return int
     * @since 100.1.0
     */
    public function getMaxNumberPasswordResetRequests();

    /**
     * Get minimum time between password reset requests
     *
     * @return int
     * @since 100.1.0
     */
    public function getMinTimeBetweenPasswordResetRequests();
}
