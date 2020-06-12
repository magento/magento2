<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;

/**
 * Get authentication data by secret
 *
 * @api
 */
interface GetAuthenticationDataBySecretInterface
{
    /**
     * Get authentication data by secret
     *
     * @param string $secret
     * @return AuthenticationDataInterface
     * @throws LocalizedException
     */
    public function execute(string $secret): AuthenticationDataInterface;
}
