<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeImsApi\Api;

use Magento\Framework\Exception\AuthorizationException;

/**
 * Declare functionality for user login from the Adobe account
 *
 * @api
 */
interface IsTokenValidInterface
{
    /**
     * Verify if access_token is valid
     *
     * @param string|null $token
     * @param string $tokenType
     * @return bool
     * @throws AuthorizationException
     */
    public function validateToken(?string $token, string $tokenType = 'access_token'): bool;
}
