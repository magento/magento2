<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * LoginAsCustomer config
 *
 * @api
 * @since 100.4.0
 */
interface ConfigInterface
{
    /**
     * Check if Login as Customer extension is enabled
     *
     * @return bool
     * @since 100.4.0
     */
    public function isEnabled(): bool;

    /**
     * Check if store view manual choice is enabled
     *
     * @return bool
     * @since 100.4.0
     */
    public function isStoreManualChoiceEnabled(): bool;

    /**
     * Get authentication data expiration time (in seconds)
     *
     * @return int
     * @since 100.4.0
     */
    public function getAuthenticationDataExpirationTime(): int;
}
