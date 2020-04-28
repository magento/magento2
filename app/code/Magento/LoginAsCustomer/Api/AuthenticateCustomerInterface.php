<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\LoginAsCustomer\Api\Data\AuthenticationDataInterface;

/**
 * Authenticate a customer
 *
 * @api
 */
interface AuthenticateCustomerInterface
{
    /**
     * Authenticate a customer
     *
     * @param AuthenticationDataInterface $authenticationData
     * @return void
     * @throws LocalizedException
     */
    public function execute(AuthenticationDataInterface $authenticationData): void;
}
