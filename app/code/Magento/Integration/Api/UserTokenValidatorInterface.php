<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Api;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Integration\Api\Data\UserToken;

/**
 * Validates tokens used to authenticate users.
 */
interface UserTokenValidatorInterface
{
    /**
     * Validate user token.
     *
     * @param UserToken $token
     * @throws AuthorizationException
     * @return void
     */
    public function validate(UserToken $token): void;
}
