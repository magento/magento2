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
 * Save authentication data. Return secret key
 *
 * @api
 * @since 100.4.0
 */
interface SaveAuthenticationDataInterface
{
    /**
     * Save authentication data. Return secret key
     *
     * @param Data\AuthenticationDataInterface $authenticationData
     * @return string
     * @throws LocalizedException
     * @since 100.4.0
     */
    public function execute(AuthenticationDataInterface $authenticationData): string;
}
