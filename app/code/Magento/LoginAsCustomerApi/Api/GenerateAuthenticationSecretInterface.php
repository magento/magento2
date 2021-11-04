<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;

/**
 * Generate authentication secret
 */
interface GenerateAuthenticationSecretInterface
{
    /**
     * Generate authentication secret
     *
     * @param AuthenticationDataInterface $authenticationData
     * @return string authentication secret
     */
    public function execute(AuthenticationDataInterface $authenticationData): string;
}
