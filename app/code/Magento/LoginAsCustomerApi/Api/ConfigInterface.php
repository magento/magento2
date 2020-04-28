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
 */
interface ConfigInterface
{
    /**
     * Check if Login As Customer extension is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Check if store view manual choice is enabled
     *
     * @return bool
     */
    public function isStoreManualChoiceEnabled(): bool;

    /**
     * Get authentication data expiration time (in seconds)
     *
     * @return int
     */
    public function getAuthenticationDataExpirationTime(): int;
}
