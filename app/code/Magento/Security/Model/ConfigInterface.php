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
 */
interface ConfigInterface
{
    /**
     * Get customer service email address
     *
     * @return string
     */
    public function getCustomerServiceEmail();

    /**
     * Get time period limitation of password reset requests
     *
     * @return int
     */
    public function getLimitationTimePeriod();

    /**
     * Check if admin account sharing is enabled
     *
     * @return bool
     */
    public function isAdminAccountSharingEnabled();

    /**
     * Get admin session lifetime
     *
     * @return int
     */
    public function getAdminSessionLifetime();

    /**
     * Get password reset protection type
     *
     * @return int
     */
    public function getPasswordResetProtectionType();

    /**
     * Get max number password reset requests per time period
     *
     * @return int
     */
    public function getMaxNumberPasswordResetRequests();

    /**
     * Get minimum time between password reset requests
     *
     * @return int
     */
    public function getMinTimeBetweenPasswordResetRequests();
}
